/*
 * KBoard Editor Scaffold (isolated asset directory)
 *
 * Script load order:
 * 1) kboard-editor-registry.js
 * 2) adapters/tiptap-adapter.js
 * 3) adapters/editorjs-adapter.js
 * 4) kboard-editor-bootstrap.js
 *
 * Example usage from existing KBoard scripts:
 *
 *   var mounted = window.KBoardEditorBootstrap.mount({
 *     adapter: 'tiptap', // or 'editorjs'
 *     selector: '#my-editor-root',
 *     textareaId: 'kboard_content'
 *   });
 *
 *   // Existing media hooks can route image URL:
 *   mounted.adapter.insertMedia('https://example.com/image.jpg');
 */
