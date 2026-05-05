import { Editor, Node, mergeAttributes } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import { Table, TableRow, TableHeader, TableCell } from '@tiptap/extension-table';
import TextAlign from '@tiptap/extension-text-align';
import Placeholder from '@tiptap/extension-placeholder';
import Link from '@tiptap/extension-link';
import Underline from '@tiptap/extension-underline';
import { TextStyle } from '@tiptap/extension-text-style';
import { SlashCommand } from './SlashCommand';
import { slashCommands } from './slashCommands';

// ─────────────────────────────────────────────────────────────────────────────
// Custom Node: Column
// ─────────────────────────────────────────────────────────────────────────────
const Column = Node.create({
    name: 'column',
    group: 'block',
    content: 'block+',
    isolating: true,
    parseHTML() {
        return [{ tag: 'div[data-type="column"]' }];
    },
    renderHTML({ HTMLAttributes }) {
        return ['div', mergeAttributes(HTMLAttributes, { 'data-type': 'column', class: 'th-editor-column' }), 0];
    },
});

// ─────────────────────────────────────────────────────────────────────────────
// Custom Node: Columns (container)
// ─────────────────────────────────────────────────────────────────────────────
const Columns = Node.create({
    name: 'columns',
    group: 'block',
    content: 'column+',
    addAttributes() {
        return { cols: { default: 2 } };
    },
    parseHTML() {
        return [{ tag: 'div[data-type="columns"]' }];
    },
    renderHTML({ HTMLAttributes, node }) {
        return ['div', mergeAttributes(HTMLAttributes, {
            'data-type': 'columns',
            'data-cols': node.attrs.cols,
            class: `th-editor-columns th-editor-columns-${node.attrs.cols}`,
        }), 0];
    },
});

// ─────────────────────────────────────────────────────────────────────────────
// Custom Image with alignment + caption
// ─────────────────────────────────────────────────────────────────────────────
const CustomImage = Image.extend({
    name: 'customImage',
    addAttributes() {
        return {
            ...this.parent?.(),
            alignment: {
                default: 'center',
                parseHTML: el => el.getAttribute('data-alignment') || 'center',
                renderHTML: attrs => ({ 'data-alignment': attrs.alignment }),
            },
            caption: {
                default: '',
                parseHTML: el => el.getAttribute('data-caption') || '',
                renderHTML: attrs => (attrs.caption ? { 'data-caption': attrs.caption } : {}),
            },
        };
    },
    parseHTML() {
        return [{ tag: 'figure[data-type="customImage"]' }];
    },
    renderHTML({ HTMLAttributes }) {
        const { src, alt, title, alignment, caption, ...rest } = HTMLAttributes;
        const figAttrs = mergeAttributes(rest, {
            'data-type': 'customImage',
            'data-alignment': alignment,
            'data-caption': caption || '',
            class: `th-image th-image-${alignment}`,
        });
        const imgAttrs = { src: src || '', alt: alt || '', title: title || '' };
        if (caption) {
            return ['figure', figAttrs, ['img', imgAttrs], ['figcaption', {}, caption]];
        }
        return ['figure', figAttrs, ['img', imgAttrs]];
    },
});

// ─────────────────────────────────────────────────────────────────────────────
// Alpine.js component factory
// ─────────────────────────────────────────────────────────────────────────────
window.articleEditor = function ({
    articleId, autosaveUrl, uploadUrl, initialContent,
    initialType, initialChangelog, initialQuestions,
    initialPinned, initialPinnedUntil, initialScheduledAt,
    initialVersion, initialReleaseDate,
    initialPlanningStatus, initialNotifEmail, initialClosesAt,
}) {
    return {
        editor: null,

        // ── Form state ──────────────────────────────────────────────────────
        articleType:     initialType         || 'annonce',
        isPinned:        !!initialPinned,
        showOptions:     false,
        version:         initialVersion      || '',
        releaseDate:     initialReleaseDate  || '',
        changelog:       initialChangelog    || { added: [], fixed: [], removed: [] },
        newChangelog:    { added: '', fixed: '', removed: '' },
        planningStatus:  initialPlanningStatus || 'prevu',
        notifEmail:      initialNotifEmail   || '',
        closesAt:        initialClosesAt     || '',
        questions:       initialQuestions    || [],
        newQuestion:     { type: 'qcm', label: '', options: [] },
        newOption:       '',
        pinnedUntil:     initialPinnedUntil  || '',
        scheduledAt:     initialScheduledAt  || '',

        syncHidden() {
            document.getElementById('type-input').value          = this.articleType;
            document.getElementById('is-pinned-input').value     = this.isPinned ? '1' : '0';
            document.getElementById('pinned-until-input').value  = this.pinnedUntil;
            document.getElementById('scheduled-at-input').value  = this.scheduledAt;
            document.getElementById('version-input').value       = this.version;
            document.getElementById('release-date-input').value  = this.releaseDate;
            document.getElementById('planning-status-input').value = this.planningStatus;
            document.getElementById('notif-email-input').value   = this.notifEmail;
            document.getElementById('closes-at-input').value     = this.closesAt;
            this.injectChangelogInputs();
            this.injectQuestionInputs();
        },

        injectChangelogInputs() {
            const form = document.getElementById('article-form');
            ['added', 'fixed', 'removed'].forEach(key => {
                form.querySelectorAll(`[name^="changelog_${key}"]`).forEach(el => el.remove());
                (this.changelog[key] || []).forEach((val, i) => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden'; inp.name = `changelog_${key}[${i}]`; inp.value = val;
                    form.appendChild(inp);
                });
            });
        },

        injectQuestionInputs() {
            const form = document.getElementById('article-form');
            form.querySelectorAll('[name^="questions["]').forEach(el => el.remove());
            this.questions.forEach((q, qi) => {
                ['type', 'label'].forEach(field => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden'; inp.name = `questions[${qi}][${field}]`; inp.value = q[field];
                    form.appendChild(inp);
                });
                (q.options || []).forEach((opt, oi) => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden'; inp.name = `questions[${qi}][options][${oi}]`; inp.value = opt;
                    form.appendChild(inp);
                });
            });
        },

        addChangelogItem(key) {
            if (this.newChangelog[key].trim()) {
                this.changelog[key].push(this.newChangelog[key].trim());
                this.newChangelog[key] = '';
            }
        },
        removeChangelogItem(key, idx) { this.changelog[key].splice(idx, 1); },
        addOption() {
            if (this.newOption.trim()) { this.newQuestion.options.push(this.newOption.trim()); this.newOption = ''; }
        },
        removeOption(idx) { this.newQuestion.options.splice(idx, 1); },
        addQuestion() {
            if (this.newQuestion.label.trim()) {
                this.questions.push({ ...this.newQuestion, options: [...this.newQuestion.options] });
                this.newQuestion = { type: 'qcm', label: '', options: [] };
                this.newOption = '';
            }
        },
        removeQuestion(idx) { this.questions.splice(idx, 1); },

        // ── TipTap state ────────────────────────────────────────────────────
        slashMenu: {
            open: false,
            items: [],
            props: null,
            x: 0,
            y: 0,
            selectedIndex: 0,
        },

        imageToolbar: {
            open: false,
            alignment: 'center',
            caption: '',
            nodePos: null,
            x: 0,
            y: 0,
            editing: false,
        },

        imageModal: {
            open: false,
            mode: 'url',
            url: '',
        },

        autosaveStatus: '',
        autosaveInterval: null,
        isDirty: false,
        openImageModalHandler: null,
        insertColumnsHandler: null,

        // ── Initialisation ─────────────────────────────────────────────────
        init() {
            const self = this;

            this.editor = new Editor({
                element: this.$refs.editorEl,
                extensions: [
                    StarterKit.configure({
                        heading: { levels: [1, 2, 3] },
                        dropcursor: { color: '#c4a84a', width: 3 },
                        link: false,
                        underline: false,
                    }),
                    CustomImage.configure({ inline: false, allowBase64: false }),
                    Table.configure({ resizable: false }),
                    TableRow,
                    TableHeader,
                    TableCell,
                    TextAlign.configure({ types: ['heading', 'paragraph'] }),
                    Placeholder.configure({
                        placeholder: ({ node }) =>
                            node.type.name === 'heading'
                                ? 'Titre…'
                                : 'Écrivez ou tapez "/" pour insérer un bloc…',
                    }),
                    Link.configure({ openOnClick: false }),
                    Underline,
                    TextStyle,
                    Columns,
                    Column,
                    SlashCommand.configure({
                        suggestion: {
                            char: '/',
                            startOfLine: true,
                            items: ({ query }) => {
                                if (!query) return slashCommands;
                                const q = query.toLowerCase();
                                return slashCommands.filter(c =>
                                    c.label.toLowerCase().includes(q) || c.desc.toLowerCase().includes(q)
                                );
                            },
                            render: () => {
                                return {
                                    onStart: (props) => {
                                        this.slashMenu.open = true;
                                        this.slashMenu.items = props.items;
                                        this.slashMenu.props = props;
                                        this.slashMenu.selectedIndex = 0;
                                        const coords = props.clientRect?.();
                                        if (coords) {
                                            this.slashMenu.x = coords.left;
                                            this.slashMenu.y = coords.bottom + 8;
                                        }
                                    },
                                    onUpdate: (props) => {
                                        this.slashMenu.items = props.items;
                                        this.slashMenu.props = props;
                                        this.slashMenu.selectedIndex = Math.min(
                                            this.slashMenu.selectedIndex,
                                            Math.max(props.items.length - 1, 0)
                                        );
                                        const coords = props.clientRect?.();
                                        if (coords) {
                                            this.slashMenu.x = coords.left;
                                            this.slashMenu.y = coords.bottom + 8;
                                        }
                                    },
                                    onExit: () => {
                                        this.slashMenu.open = false;
                                        this.slashMenu.items = [];
                                        this.slashMenu.props = null;
                                        this.slashMenu.selectedIndex = 0;
                                    },
                                    onKeyDown: ({ event }) => {
                                        const cmds = this.slashMenu.items || [];
                                        if (event.key === 'ArrowDown') {
                                            this.slashMenu.selectedIndex = Math.min(this.slashMenu.selectedIndex + 1, cmds.length - 1);
                                            return true;
                                        }
                                        if (event.key === 'ArrowUp') {
                                            this.slashMenu.selectedIndex = Math.max(this.slashMenu.selectedIndex - 1, 0);
                                            return true;
                                        }
                                        if (event.key === 'Enter' && cmds.length) {
                                            this.selectSlashItem(this.slashMenu.selectedIndex);
                                            return true;
                                        }
                                        if (event.key === 'Escape') {
                                            this.slashMenu.open = false;
                                            return true;
                                        }
                                        return false;
                                    },
                                };
                            },
                        },
                    }),
                ],
                content: initialContent || null,
                autofocus: 'end',

                onUpdate({ editor }) {
                    self.isDirty = true;
                    self.syncContentInput(editor);
                },

                onSelectionUpdate({ editor }) {
                    self.detectImageSelection(editor);
                },
            });

            this.setupDropZone();
            if (autosaveUrl) this.startAutosave();

            if (this.openImageModalHandler) {
                this.$refs.editorEl.removeEventListener('open-image-modal', this.openImageModalHandler);
            }
            this.openImageModalHandler = () => {
                this.imageModal.open = true;
            };
            this.$refs.editorEl.addEventListener('open-image-modal', this.openImageModalHandler);

            if (this.insertColumnsHandler) {
                this.$refs.editorEl.removeEventListener('insert-columns', this.insertColumnsHandler);
            }
            this.insertColumnsHandler = (event) => {
                this.insertColumns(event.detail?.count || 2);
            };
            this.$refs.editorEl.addEventListener('insert-columns', this.insertColumnsHandler);
        },

        // ── Sync hidden input ──────────────────────────────────────────────
        syncContentInput(editor) {
            const el = document.getElementById('content-input');
            if (el) el.value = JSON.stringify(editor.getJSON());
        },

        // ── Slash select ───────────────────────────────────────────────────
        selectSlashItem(index) {
            const props = this.slashMenu.props;
            const item = this.slashMenu.items?.[index];
            if (!props || !item) return;

            props.command(item);
            this.slashMenu.open = false;
        },

        insertColumns(count) {
            const columns = Array.from({ length: count }, () => ({
                type: 'column',
                content: [{ type: 'paragraph' }],
            }));
            this.editor.chain().focus().insertContent({
                type: 'columns',
                attrs: { cols: count },
                content: columns,
            }).run();
        },

        // ── Image selection + toolbar ──────────────────────────────────────
        detectImageSelection(editor) {
            const { selection } = editor.state;
            const node = selection.node;
            if (node && node.type.name === 'customImage') {
                const coords = editor.view.coordsAtPos(selection.from);
                this.imageToolbar.open = true;
                this.imageToolbar.alignment = node.attrs.alignment || 'center';
                this.imageToolbar.caption = node.attrs.caption || '';
                this.imageToolbar.nodePos = selection.from;
                this.imageToolbar.x = coords.left;
                this.imageToolbar.y = Math.max(coords.top - 48, 60);
            } else if (!this.imageToolbar.editing) {
                this.imageToolbar.open = false;
            }
        },

        setImageAlignment(alignment) {
            this.editor.chain().focus().updateAttributes('customImage', { alignment }).run();
            this.imageToolbar.alignment = alignment;
        },

        setImageCaption(caption) {
            this.editor.chain().focus().updateAttributes('customImage', { caption }).run();
            this.imageToolbar.caption = caption;
        },

        // ── Insert image ───────────────────────────────────────────────────
        insertImageByUrl(url) {
            if (!url) return;
            this.editor.chain().focus().insertContent({
                type: 'customImage',
                attrs: { src: url, alt: '', alignment: 'center', caption: '' },
            }).run();
            this.imageModal.open = false;
            this.imageModal.url = '';
        },

        async insertImageByFile(file) {
            if (!file) return;
            const formData = new FormData();
            formData.append('image', file);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            try {
                const res = await fetch(uploadUrl, { method: 'POST', body: formData });
                const data = await res.json();
                if (data.url) this.insertImageByUrl(data.url);
            } catch (e) {
                console.error('Upload image failed', e);
            }
            this.imageModal.open = false;
        },

        // ── Drag & drop image from desktop ─────────────────────────────────
        setupDropZone() {
            const el = this.$refs.editorEl;
            el.addEventListener('dragover', e => {
                if (e.dataTransfer?.types.includes('Files')) {
                    e.preventDefault();
                    el.classList.add('th-drop-active');
                }
            });
            el.addEventListener('dragleave', () => el.classList.remove('th-drop-active'));
            el.addEventListener('drop', async e => {
                el.classList.remove('th-drop-active');
                const images = Array.from(e.dataTransfer?.files || []).filter(f => f.type.startsWith('image/'));
                if (!images.length) return;
                e.preventDefault();
                for (const img of images) await this.insertImageByFile(img);
            });
        },

        // ── Autosave ───────────────────────────────────────────────────────
        startAutosave() {
            this.autosaveInterval = setInterval(async () => {
                if (!this.isDirty || !this.editor) return;
                const content = JSON.stringify(this.editor.getJSON());
                const token = document.querySelector('meta[name="csrf-token"]').content;
                try {
                    const res = await fetch(autosaveUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                        body: JSON.stringify({ content }),
                    });
                    const data = await res.json();
                    this.autosaveStatus = `Sauvegardé à ${data.saved_at}`;
                    this.isDirty = false;
                } catch {
                    this.autosaveStatus = 'Erreur de sauvegarde';
                }
            }, 30000);
        },

        destroy() {
            clearInterval(this.autosaveInterval);
            if (this.openImageModalHandler && this.$refs?.editorEl) {
                this.$refs.editorEl.removeEventListener('open-image-modal', this.openImageModalHandler);
            }
            if (this.insertColumnsHandler && this.$refs?.editorEl) {
                this.$refs.editorEl.removeEventListener('insert-columns', this.insertColumnsHandler);
            }
            this.editor?.destroy();
        },
    };
};

// ─── Legacy export kept for backward compat ───────────────────────────────────
window.initBlogTipTap = function (container, initialHtml, onUpdate) {
    if (!container) return null;
    const editor = new Editor({
        element: container,
        extensions: [StarterKit.configure({ link: false, underline: false }), Placeholder.configure({ placeholder: 'Écris ton contenu…' }), Link, Underline, TextStyle],
        content: initialHtml || '<p></p>',
        onUpdate({ editor: e }) {
            if (typeof onUpdate === 'function') onUpdate(e.getHTML());
        },
    });
    return editor;
};

