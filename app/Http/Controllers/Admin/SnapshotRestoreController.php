<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Personnage;
use App\Models\Snapshot;
use App\Services\SnapshotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SnapshotRestoreController extends Controller
{
    public function globalIndex(Request $request): View
    {
        $query = Snapshot::query()
            ->with([
                'personnage:id_perso,nom_perso,slug',
                'admin:id_admin,pseudo_admin',
            ]);

        if ($request->filled('action_type')) {
            $query->where('action_type', (string) $request->input('action_type'));
        }

        if ($request->filled('personnage')) {
            $search = (string) $request->input('personnage');
            $query->whereHas('personnage', function ($q) use ($search): void {
                $q->where('nom_perso', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('admin')) {
            $adminSearch = (string) $request->input('admin');
            $query->whereHas('admin', function ($q) use ($adminSearch): void {
                $q->where('pseudo_admin', 'like', '%' . $adminSearch . '%');
            });
        }

        $sort = (string) $request->input('sort', 'latest');
        if ($sort === 'oldest') {
            $query->orderBy('id_snapshot');
        } else {
            $query->orderByDesc('id_snapshot');
        }

        $snapshots = $query->paginate(30)->withQueryString();
        $groupedSnapshots = $snapshots->getCollection()->groupBy('fid_perso');

        return view('admin.snapshots.global', [
            'snapshots' => $snapshots,
            'groupedSnapshots' => $groupedSnapshots,
            'sort' => $sort,
        ]);
    }

    public function index(Request $request, Personnage $personnage): View
    {
        $query = Snapshot::query()
            ->where('fid_perso', $personnage->id_perso)
            ->with(['admin:id_admin,pseudo_admin'])
            ->orderByDesc('id_snapshot');

        if ($request->filled('action_type')) {
            $query->where('action_type', (string) $request->input('action_type'));
        }

        $snapshots = $query->paginate(20)->withQueryString();

        return view('admin.snapshots.index', [
            'personnage' => $personnage,
            'snapshots' => $snapshots,
        ]);
    }

    public function show(Snapshot $snapshot): View
    {
        $snapshot->load([
            'personnage:id_perso,nom_perso,slug',
            'admin:id_admin,pseudo_admin',
            'modifications',
        ]);

        return view('admin.snapshots.show', [
            'snapshot' => $snapshot,
        ]);
    }

    public function restore(Snapshot $snapshot, SnapshotService $snapshotService): RedirectResponse
    {
        $snapshot->load('modifications');
        $snapshotService->restore($snapshot);

        return redirect()
            ->route('admin.snapshots.show', $snapshot)
            ->with('success', 'Snapshot restauré avec succès.');
    }
}
