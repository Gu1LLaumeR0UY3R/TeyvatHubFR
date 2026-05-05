export const slashCommands = [
    {
        id: 'h1',
        label: 'Titre 1',
        icon: 'H1',
        desc: 'Grand titre',
        command: ({ editor, range }) => {
            editor.chain().focus().deleteRange(range).setHeading({ level: 1 }).run();
        },
    },
    {
        id: 'h2',
        label: 'Titre 2',
        icon: 'H2',
        desc: 'Titre moyen',
        command: ({ editor, range }) => {
            editor.chain().focus().deleteRange(range).setHeading({ level: 2 }).run();
        },
    },
    {
        id: 'h3',
        label: 'Titre 3',
        icon: 'H3',
        desc: 'Petit titre',
        command: ({ editor, range }) => {
            editor.chain().focus().deleteRange(range).setHeading({ level: 3 }).run();
        },
    },
    {
        id: 'image',
        label: 'Image',
        icon: '🖼',
        desc: 'Upload ou URL',
        command: ({ editor, range }) => {
            editor.chain().focus().deleteRange(range).run();
            editor.view.dom.dispatchEvent(new CustomEvent('open-image-modal', { bubbles: true }));
        },
    },
    {
        id: 'bullet',
        label: 'Liste à puces',
        icon: '•',
        desc: 'Liste non ordonnée',
        command: ({ editor, range }) => {
            editor.chain().focus().deleteRange(range).toggleBulletList().run();
        },
    },
    {
        id: 'ordered',
        label: 'Liste numérotée',
        icon: '1.',
        desc: 'Liste ordonnée',
        command: ({ editor, range }) => {
            editor.chain().focus().deleteRange(range).toggleOrderedList().run();
        },
    },
    {
        id: 'table',
        label: 'Tableau',
        icon: '⊞',
        desc: 'Tableau éditable',
        command: ({ editor, range }) => {
            editor.chain().focus().deleteRange(range).insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run();
        },
    },
    {
        id: 'hr',
        label: 'Séparateur',
        icon: '─',
        desc: 'Ligne horizontale',
        command: ({ editor, range }) => {
            editor.chain().focus().deleteRange(range).setHorizontalRule().run();
        },
    },
    {
        id: 'cols2',
        label: '2 Colonnes',
        icon: '▌▌',
        desc: 'Mise en page 2 colonnes',
        command: ({ editor, range }) => {
            editor.chain().focus().deleteRange(range).run();
            editor.view.dom.dispatchEvent(new CustomEvent('insert-columns', { detail: { count: 2 }, bubbles: true }));
        },
    },
    {
        id: 'cols3',
        label: '3 Colonnes',
        icon: '▌▌▌',
        desc: 'Mise en page 3 colonnes',
        command: ({ editor, range }) => {
            editor.chain().focus().deleteRange(range).run();
            editor.view.dom.dispatchEvent(new CustomEvent('insert-columns', { detail: { count: 3 }, bubbles: true }));
        },
    },
];
