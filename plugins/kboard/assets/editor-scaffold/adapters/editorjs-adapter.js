(function (global) {
  'use strict';

  var registry = global.KBoardEditorAdapters;
  if (!registry) {
    throw new Error('[KBoardEditorJSAdapter] Load kboard-editor-registry.js first.');
  }

  function parseListItems(listNode) {
    var items = [];
    for (var i = 0; i < listNode.children.length; i += 1) {
      items.push(listNode.children[i].innerHTML);
    }
    return items;
  }

  function parseWarningData(node) {
    var title = '';
    var message = '';
    var titleNode = node.querySelector('strong, b');

    if (titleNode) {
      title = (titleNode.textContent || '').trim();
    }

    var clone = node.cloneNode(true);
    var cloneTitle = clone.querySelector('strong, b');
    if (cloneTitle && cloneTitle.parentNode) {
      cloneTitle.parentNode.removeChild(cloneTitle);
    }

    message = (clone.textContent || '').trim();

    return {
      title: title,
      message: message
    };
  }

  function isWarningNode(node) {
    var cls = (node.className || '').toLowerCase();
    var marker = (node.getAttribute('data-kboard-warning') || '').toLowerCase();

    return cls.indexOf('kboard-warning') !== -1 || cls.indexOf('cdx-warning') !== -1 || marker === '1' || marker === 'true';
  }

  function parseTableData(tableNode) {
    var rows = tableNode.querySelectorAll('tr');
    var content = [];
    var withHeadings = false;

    for (var i = 0; i < rows.length; i += 1) {
      var row = rows[i];
      var cells = row.querySelectorAll('th, td');
      var rowData = [];
      var rowHasHeading = false;

      for (var j = 0; j < cells.length; j += 1) {
        var cell = cells[j];
        if (cell.tagName && cell.tagName.toLowerCase() === 'th') {
          rowHasHeading = true;
        }
        rowData.push((cell.textContent || '').trim());
      }

      if (rowData.length) {
        if (i === 0 && rowHasHeading) {
          withHeadings = true;
        }
        content.push(rowData);
      }
    }

    return {
      withHeadings: withHeadings,
      content: content
    };
  }

  function getChecklistItemContent(itemNode) {
    var clone = itemNode.cloneNode(true);
    var checkboxes = clone.querySelectorAll('input[type="checkbox"]');
    var nestedLists = clone.querySelectorAll('ul, ol');

    for (var i = 0; i < checkboxes.length; i += 1) {
      if (checkboxes[i].parentNode) {
        checkboxes[i].parentNode.removeChild(checkboxes[i]);
      }
    }

    for (var j = 0; j < nestedLists.length; j += 1) {
      if (nestedLists[j].parentNode) {
        nestedLists[j].parentNode.removeChild(nestedLists[j]);
      }
    }

    return (clone.textContent || '').trim();
  }

  function parseChecklistItems(listNode) {
    var items = [];
    var children = listNode.children;

    for (var i = 0; i < children.length; i += 1) {
      var itemNode = children[i];
      if (!itemNode.tagName || itemNode.tagName.toLowerCase() !== 'li') {
        continue;
      }

      var checkbox = itemNode.querySelector('input[type="checkbox"]');
      var nestedItems = [];
      for (var j = 0; j < itemNode.children.length; j += 1) {
        var child = itemNode.children[j];
        var childTag = child.tagName ? child.tagName.toLowerCase() : '';
        if (childTag === 'ul' || childTag === 'ol') {
          nestedItems = nestedItems.concat(parseChecklistItems(child));
        }
      }

      items.push({
        content: getChecklistItemContent(itemNode),
        meta: { checked: !!(checkbox && checkbox.checked) },
        items: nestedItems
      });
    }

    return items;
  }

  function getListItemContent(item) {
    if (typeof item === 'string') {
      return item;
    }
    if (item && typeof item.content === 'string') {
      return item.content;
    }
    if (item && typeof item.text === 'string') {
      return item.text;
    }
    return '';
  }

  function getListItemChildren(item) {
    if (item && Array.isArray(item.items)) {
      return item.items;
    }
    return [];
  }

  function isListItemChecked(item) {
    if (!item || typeof item !== 'object') return false;
    if (item.meta && typeof item.meta.checked !== 'undefined') {
      return !!item.meta.checked;
    }
    if (typeof item.checked !== 'undefined') {
      return !!item.checked;
    }
    return false;
  }

  function renderListItems(items, style) {
    var listStyle = style === 'ordered' ? 'ordered' : (style === 'checklist' ? 'checklist' : 'unordered');

    return (items || []).map(function (item) {
      var rawContent = getListItemContent(item);
      var children = getListItemChildren(item);
      var childTag = listStyle === 'ordered' ? 'ol' : 'ul';
      var content = rawContent;

      if (listStyle === 'checklist') {
        content = registry.utils.escapeHTML(rawContent);
        content = (isListItemChecked(item) ? '&#x2611; ' : '&#x2610; ') + content;
      }

      var childListHTML = '';
      if (children.length) {
        childListHTML = '<' + childTag + '>' + renderListItems(children, listStyle) + '</' + childTag + '>';
      }

      return '<li>' + content + childListHTML + '</li>';
    }).join('');
  }

  function bestEffortHTMLToBlocks(html) {
    var parser = new DOMParser();
    var doc = parser.parseFromString(html || '', 'text/html');
    var blocks = [];
    var nodes = doc.body.childNodes;

    for (var i = 0; i < nodes.length; i += 1) {
      var node = nodes[i];
      if (node.nodeType !== 1) continue;

      var tag = node.tagName.toLowerCase();
      if (tag === 'p') {
        blocks.push({ type: 'paragraph', data: { text: node.innerHTML } });
      } else if (/^h[1-6]$/.test(tag)) {
        blocks.push({
          type: 'header',
          data: { text: node.innerHTML, level: parseInt(tag.substring(1), 10) }
        });
      } else if (tag === 'blockquote') {
        if (isWarningNode(node)) {
          blocks.push({
            type: 'warning',
            data: parseWarningData(node)
          });
          continue;
        }

        blocks.push({
          type: 'quote',
          data: {
            text: node.innerHTML,
            caption: '',
            alignment: 'left'
          }
        });
      } else if (tag === 'ul' || tag === 'ol') {
        var isChecklist = !!node.querySelector('input[type="checkbox"]');
        blocks.push({
          type: 'list',
          data: {
            style: isChecklist ? 'checklist' : (tag === 'ol' ? 'ordered' : 'unordered'),
            items: isChecklist ? parseChecklistItems(node) : parseListItems(node)
          }
        });
      } else if (tag === 'hr') {
        blocks.push({ type: 'delimiter', data: {} });
      } else if (tag === 'pre') {
        var codeNode = node.querySelector('code');
        blocks.push({
          type: 'code',
          data: {
            code: (codeNode ? codeNode.textContent : node.textContent) || ''
          }
        });
      } else if (tag === 'table') {
        blocks.push({
          type: 'table',
          data: parseTableData(node)
        });
      } else if (tag === 'div' && isWarningNode(node)) {
        blocks.push({
          type: 'warning',
          data: parseWarningData(node)
        });
      } else if (tag === 'img') {
        blocks.push({
          type: 'image',
          data: {
            file: { url: node.getAttribute('src') || '' },
            caption: node.getAttribute('alt') || '',
            withBorder: false,
            withBackground: false,
            stretched: false
          }
        });
      } else {
        blocks.push({ type: 'paragraph', data: { text: node.outerHTML } });
      }
    }

    if (!blocks.length) {
      blocks.push({ type: 'paragraph', data: { text: html || '' } });
    }

    return blocks;
  }

  function blocksToHTML(data) {
    if (!data || !Array.isArray(data.blocks)) return '';

    return data.blocks.map(function (block) {
      if (!block || !block.type) return '';

      if (block.type === 'paragraph') {
        return '<p>' + (block.data && block.data.text ? block.data.text : '') + '</p>';
      }
      if (block.type === 'header') {
        var level = (block.data && block.data.level) || 2;
        if (level < 1 || level > 6) level = 2;
        return '<h' + level + '>' + ((block.data && block.data.text) || '') + '</h' + level + '>';
      }
      if (block.type === 'quote') {
        return '<blockquote>' + ((block.data && block.data.text) || '') + '</blockquote>';
      }
      if (block.type === 'list') {
        var listStyle = block.data && block.data.style ? block.data.style : 'unordered';
        var style = listStyle === 'ordered' ? 'ol' : 'ul';
        var items = (block.data && block.data.items) || [];
        var itemHTML = renderListItems(items, listStyle);
        return '<' + style + '>' + itemHTML + '</' + style + '>';
      }
      if (block.type === 'image') {
        var src = block.data && block.data.file && block.data.file.url ? block.data.file.url : '';
        var caption = block.data && block.data.caption ? block.data.caption : '';
        return '<p><img src="' + registry.utils.escapeHTML(src) + '" alt="' + registry.utils.escapeHTML(caption) + '" /></p>';
      }
      if (block.type === 'delimiter') {
        return '<hr />';
      }
      if (block.type === 'code') {
        var code = block.data && typeof block.data.code === 'string' ? block.data.code : '';
        return '<pre><code>' + registry.utils.escapeHTML(code) + '</code></pre>';
      }
      if (block.type === 'table') {
        var tableRows = (block.data && Array.isArray(block.data.content)) ? block.data.content : [];
        var withHeadings = !!(block.data && block.data.withHeadings);

        if (!tableRows.length) {
          return '<table><tbody></tbody></table>';
        }

        var headHTML = '';
        var bodyRows = tableRows;

        if (withHeadings) {
          var headingCells = (tableRows[0] || []).map(function (cell) {
            return '<th>' + registry.utils.escapeHTML(cell) + '</th>';
          }).join('');
          headHTML = '<thead><tr>' + headingCells + '</tr></thead>';
          bodyRows = tableRows.slice(1);
        }

        var bodyHTML = bodyRows.map(function (row) {
          var cellHTML = (row || []).map(function (cell) {
            return '<td>' + registry.utils.escapeHTML(cell) + '</td>';
          }).join('');
          return '<tr>' + cellHTML + '</tr>';
        }).join('');

        return '<table>' + headHTML + '<tbody>' + bodyHTML + '</tbody></table>';
      }
      if (block.type === 'warning') {
        var title = block.data && block.data.title ? block.data.title : '';
        var message = block.data && block.data.message ? block.data.message : '';
        var titleHTML = title ? '<strong>' + registry.utils.escapeHTML(title) + '</strong>' : '';
        var breakHTML = title && message ? '<br />' : '';
        return '<blockquote class="kboard-warning" data-kboard-warning="1">' + titleHTML + breakHTML + registry.utils.escapeHTML(message) + '</blockquote>';
      }

      return '';
    }).join('');
  }

  registry.register('editorjs', function editorjsFactory(options) {
    var EditorJS = global.EditorJS;
    var editor = null;
    var element = registry.utils.resolveElement(options.element || options.selector);
    var textarea = options.textarea;
    var initialHTML = typeof options.initialHTML === 'string' ? options.initialHTML : textarea.value || '';
    var holderId = options.holderId || (element && element.id) || ('kboard-editorjs-holder-' + Date.now());
    var lastSavedData = { blocks: [] };

    if (!element) {
      throw new Error('[KBoardEditorJSAdapter] Missing mount element.');
    }
    if (!element.id) {
      element.id = holderId;
    }

    function saveData() {
      if (!editor) {
        return Promise.resolve(lastSavedData);
      }
      return editor.save().then(function (data) {
        lastSavedData = data;
        return data;
      });
    }

    return {
      init: function init() {
        if (!EditorJS) {
          throw new Error('[KBoardEditorJSAdapter] Editor.js global not found. Include Editor.js scripts before this adapter.');
        }

        var blocks = bestEffortHTMLToBlocks(initialHTML);

        var tools = {};

        if (global.Header) {
          tools.header = { class: global.Header, inlineToolbar: ['link'] };
        }
        if (global.Quote) {
          tools.quote = { class: global.Quote, inlineToolbar: true };
        }
        if (global.List) {
          tools.list = { class: global.List, inlineToolbar: true };
        }
        if (global.Delimiter) {
          tools.delimiter = { class: global.Delimiter };
        }
        if (global.CodeTool || global.Code) {
          tools.code = { class: global.CodeTool || global.Code };
        }
        if (global.Table) {
          tools.table = { class: global.Table, inlineToolbar: true };
        }
        if (global.Warning) {
          tools.warning = { class: global.Warning, inlineToolbar: true };
        }
        if (global.ImageTool) {
          /* Image tool — hidden from toolbox, used for rendering image blocks + drag-and-drop */
          tools.image = {
            class: global.ImageTool,
            toolbox: false,
            config: {
              uploader: {
                uploadByFile: function uploadByFile(file) {
                  /* Ignore internal drag (browser generates 'image.png' or empty name for dragged-in page images) */
                  var fname = (file && file.name) || '';
                  if (/^image\.(png|jpeg|jpg|gif|bmp|webp)$/i.test(fname) && file.lastModified && (Date.now() - file.lastModified < 1000)) {
                    return Promise.reject(new Error('internal-drag'));
                  }

                  var settings = global.kboard_settings || {};
                  var current = global.kboard_current || {};
                  if (!settings.ajax_url || !settings.ajax_security) {
                    return Promise.reject(new Error('KBoard AJAX settings not found.'));
                  }
                  var formData = new FormData();
                  formData.append('action', 'kboard_media_ajax_upload');
                  formData.append('security', settings.ajax_security);
                  formData.append('board_id', current.board_id || '');
                  formData.append('media_group', settings.media_group || '');
                  formData.append('content_uid', current.content_uid || '');
                  formData.append('image', file);
                  return fetch(settings.ajax_url, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                  }).then(function (res) { return res.json(); }).then(function (data) {
                    if (data && data.success === 1) return data;
                    return Promise.reject(new Error((data && data.message) || 'Upload failed'));
                  });
                },
                uploadByUrl: function uploadByUrl(url) {
                  return Promise.resolve({ success: 1, file: { url: url } });
                }
              }
            }
          };

          /* KBoard media selector — visible in toolbox, opens KBoard media popup */
          var KBoardMediaTool = function KBoardMediaTool(params) {
            this.api = params.api;
          };
          KBoardMediaTool.prototype.render = function render() {
            if (typeof global.kboard_editor_open_media === 'function') {
              global.kboard_editor_open_media();
            }
            var div = document.createElement('div');
            var api = this.api;
            setTimeout(function () {
              var idx = api.blocks.getCurrentBlockIndex();
              if (idx >= 0) api.blocks.delete(idx);
            }, 100);
            return div;
          };
          KBoardMediaTool.prototype.save = function save() { return undefined; };
          KBoardMediaTool.prototype.validate = function validate() { return false; };

          try {
            Object.defineProperty(KBoardMediaTool, 'toolbox', {
              get: function () {
                return {
                  title: '\uc774\ubbf8\uc9c0',
                  icon: '<svg width="17" height="15" viewBox="0 0 336 276"><path d="M291 150V79c0-19-15-34-34-34H79c-19 0-34 15-34 34v42l67-44 81 72 56-29 42 30zm0 52l-43-30-56 30-81-67-66 39v23c0 19 15 34 34 34h178c17 0 31-13 34-29zM79 0h178c44 0 79 35 79 79v118c0 44-35 79-79 79H79c-44 0-79-35-79-79V79C0 35 35 0 79 0z"/></svg>'
                };
              }
            });
          } catch (e) {
            KBoardMediaTool.toolbox = {
              title: '\uc774\ubbf8\uc9c0',
              icon: '<svg width="17" height="15" viewBox="0 0 336 276"><path d="M291 150V79c0-19-15-34-34-34H79c-19 0-34 15-34 34v42l67-44 81 72 56-29 42 30zm0 52l-43-30-56 30-81-67-66 39v23c0 19 15 34 34 34h178c17 0 31-13 34-29zM79 0h178c44 0 79 35 79 79v118c0 44-35 79-79 79H79c-44 0-79-35-79-79V79C0 35 35 0 79 0z"/></svg>'
            };
          }

          tools.kboardImage = { class: KBoardMediaTool };
        }

        editor = new EditorJS({
          holder: element.id,
          autofocus: !!options.autofocus,
          data: { blocks: blocks },
          tools: tools,
          onReady: function onReady() {
            lastSavedData = { blocks: blocks };
            textarea.value = blocksToHTML(lastSavedData);
          },
          onChange: function onChange() {
            saveData().then(function (data) {
              textarea.value = blocksToHTML(data);
            });
          }
        });
      },
      getHTML: function getHTML() {
        return blocksToHTML(lastSavedData);
      },
      setHTML: function setHTML(html) {
        var nextHTML = typeof html === 'string' ? html : '';
        var blocks = bestEffortHTMLToBlocks(nextHTML);
        lastSavedData = { blocks: blocks };

        if (editor && editor.blocks && typeof editor.blocks.render === 'function') {
          return editor.blocks.render(lastSavedData).then(function () {
            textarea.value = blocksToHTML(lastSavedData);
          });
        }

        textarea.value = blocksToHTML(lastSavedData);
      },
      insertMedia: function insertMedia(url) {
        if (!url) return;
        var safeURL = String(url);

        if (editor && editor.blocks && typeof editor.blocks.insert === 'function') {
          editor.blocks.insert('image', {
            file: { url: safeURL },
            caption: '',
            withBorder: false,
            withBackground: false,
            stretched: false
          });
          return;
        }

        lastSavedData.blocks.push({
          type: 'image',
          data: {
            file: { url: safeURL },
            caption: '',
            withBorder: false,
            withBackground: false,
            stretched: false
          }
        });
        textarea.value = blocksToHTML(lastSavedData);
      },
      beforeSubmit: function beforeSubmit() {
        var self = this;
        return saveData().then(function (data) {
          textarea.value = blocksToHTML(data);
          return self.getHTML();
        });
      },
      destroy: function destroy() {
        if (!editor || typeof editor.destroy !== 'function') {
          editor = null;
          return;
        }

        editor.destroy();
        editor = null;
      }
    };
  });
})(window);
