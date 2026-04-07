(function (global) {
  'use strict';

  var registry = global.KBoardEditorAdapters;
  if (!registry) {
    throw new Error('[KBoardEditorBootstrap] Load kboard-editor-registry.js first.');
  }

  function resolveForm(options, textarea) {
    if (options.form && options.form.nodeType === 1) return options.form;
    if (options.formSelector) return document.querySelector(options.formSelector);
    if (textarea.form) return textarea.form;
    return textarea.closest ? textarea.closest('form') : null;
  }

  function mount(options) {
    var opts = Object.assign({ textareaId: 'kboard_content' }, options || {});
    var textarea = registry.utils.ensureTextarea(opts);
    var mountEl = registry.utils.resolveElement(opts.element || opts.selector);

    if (!mountEl) {
      mountEl = document.createElement('div');
      mountEl.className = 'kboard-editor-scaffold';
      textarea.parentNode.insertBefore(mountEl, textarea.nextSibling);
    }

    textarea.style.display = 'none';

    var instance = registry.create(opts.adapter, Object.assign({}, opts, {
      element: mountEl,
      textarea: textarea,
      initialHTML: textarea.value
    }));

    instance.init();

    var form = resolveForm(opts, textarea);
    var submitHandler = null;

    if (form) {
      submitHandler = function submitHandler(event) {
        if (form.__kboardEditorForwardSubmit === true) {
          form.__kboardEditorForwardSubmit = false;
          return;
        }

        var result = instance.beforeSubmit();
        if (!registry.utils.isPromiseLike(result)) {
          return;
        }

        event.preventDefault();
        result.then(function () {
          form.__kboardEditorForwardSubmit = true;
          form.submit();
        }).catch(function (error) {
          form.__kboardEditorForwardSubmit = false;
          if (global.console && console.error) {
            console.error('[KBoardEditorBootstrap] beforeSubmit failed:', error);
          }
        });
      };

      form.addEventListener('submit', submitHandler);
    }

    return {
      adapter: instance,
      form: form,
      element: mountEl,
      textarea: textarea,
      destroy: function destroy() {
        if (form && submitHandler) {
          form.removeEventListener('submit', submitHandler);
        }
        instance.destroy();
        textarea.style.display = '';
      }
    };
  }

  global.KBoardEditorBootstrap = {
    mount: mount
  };
})(window);
