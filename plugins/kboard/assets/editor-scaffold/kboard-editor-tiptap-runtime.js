import * as TipTapCore from 'https://esm.sh/@tiptap/core@2.0.0?target=es2022&no-check';
import StarterKit from 'https://esm.sh/@tiptap/starter-kit@2.0.0?target=es2022&no-check';
import Link from 'https://esm.sh/@tiptap/extension-link@2.0.0?target=es2022&no-check';
import Underline from 'https://esm.sh/@tiptap/extension-underline@2.0.0?target=es2022&no-check';
import Image from 'https://esm.sh/@tiptap/extension-image@2.0.0?target=es2022&no-check';

const global = window;
const ESM = 'https://esm.sh/';
const V = '@2.0.0?target=es2022&no-check';

async function opt(pkg) {
  try { return (await import(ESM + pkg + V)).default; }
  catch (e) { return null; }
}

global.kboardTipTapReady = (async () => {
  const [
    TextAlign, TextStyle, Color, Highlight,
    Subscript, Superscript,
    Table, TableRow, TableCell, TableHeader,
    TaskList, TaskItem
  ] = await Promise.all([
    opt('@tiptap/extension-text-align'),
    opt('@tiptap/extension-text-style'),
    opt('@tiptap/extension-color'),
    opt('@tiptap/extension-highlight'),
    opt('@tiptap/extension-subscript'),
    opt('@tiptap/extension-superscript'),
    opt('@tiptap/extension-table'),
    opt('@tiptap/extension-table-row'),
    opt('@tiptap/extension-table-cell'),
    opt('@tiptap/extension-table-header'),
    opt('@tiptap/extension-task-list'),
    opt('@tiptap/extension-task-item'),
  ]);

  const extensions = {
    StarterKit, Link, Image, Underline,
    TextAlign, TextStyle, Color, Highlight,
    Subscript, Superscript,
    Table, TableRow, TableCell, TableHeader,
    TaskList, TaskItem,
  };

  global.tiptap = TipTapCore;
  global.TiptapCore = TipTapCore;
  global.tiptapExtensions = extensions;
  global.TiptapExtensions = extensions;

  if (!global.StarterKit) global.StarterKit = StarterKit;
  if (!global.Link) global.Link = Link;
  if (!global.Image) global.Image = Image;
  if (!global.Underline) global.Underline = Underline;
})();
