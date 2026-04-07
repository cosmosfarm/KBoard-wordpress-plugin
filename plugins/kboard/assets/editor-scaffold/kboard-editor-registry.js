(function (global) {
  'use strict';

  var REGISTRY_KEY = 'KBoardEditorAdapters';

  function isPromiseLike(value) {
    return !!value && typeof value.then === 'function';
  }

  function resolveElement(target) {
    if (!target) return null;
    if (typeof target === 'string') return document.querySelector(target);
    return target.nodeType === 1 ? target : null;
  }

  function escapeHTML(value) {
    return String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function ensureTextarea(options) {
    var id = (options && options.textareaId) || 'kboard_content';
    var textarea = (options && options.textarea) || document.getElementById(id);

    if (!textarea) {
      throw new Error('[KBoardEditorAdapters] textarea#' + id + ' not found.');
    }

    return textarea;
  }

  function normalizeAdapter(instance) {
    if (!instance || typeof instance !== 'object') {
      throw new Error('[KBoardEditorAdapters] Adapter factory must return an object.');
    }

    var required = ['init', 'getHTML', 'setHTML', 'insertMedia', 'beforeSubmit', 'destroy'];
    for (var i = 0; i < required.length; i += 1) {
      if (typeof instance[required[i]] !== 'function') {
        throw new Error('[KBoardEditorAdapters] Missing method "' + required[i] + '" on adapter instance.');
      }
    }

    return instance;
  }

  function createRegistry() {
    var factories = {};

    return {
      version: '0.1.0',
      register: function register(name, factory) {
        if (!name || typeof name !== 'string') {
          throw new Error('[KBoardEditorAdapters] Adapter name must be a non-empty string.');
        }
        if (typeof factory !== 'function') {
          throw new Error('[KBoardEditorAdapters] Adapter factory must be a function.');
        }

        factories[name] = factory;
        return this;
      },
      has: function has(name) {
        return Object.prototype.hasOwnProperty.call(factories, name);
      },
      list: function list() {
        return Object.keys(factories);
      },
      create: function create(name, options) {
        if (!this.has(name)) {
          throw new Error('[KBoardEditorAdapters] Unknown adapter: ' + name);
        }
        var textarea = ensureTextarea(options || {});
        var instance = factories[name](Object.assign({}, options || {}, { textarea: textarea }));
        instance = normalizeAdapter(instance);

        return this.attachSync(instance, textarea);
      },
      attachSync: function attachSync(instance, textarea) {
        var originalBeforeSubmit = instance.beforeSubmit;

        instance.syncToTextarea = function syncToTextarea() {
          var html = instance.getHTML();
          textarea.value = typeof html === 'string' ? html : '';
          return textarea.value;
        };

        instance.beforeSubmit = function wrappedBeforeSubmit() {
          var result = originalBeforeSubmit.apply(instance, arguments);
          if (isPromiseLike(result)) {
            return result.then(function () {
              return instance.syncToTextarea();
            });
          }

          return instance.syncToTextarea();
        };

        return instance;
      },
      utils: {
        ensureTextarea: ensureTextarea,
        resolveElement: resolveElement,
        escapeHTML: escapeHTML,
        isPromiseLike: isPromiseLike
      }
    };
  }

  global[REGISTRY_KEY] = global[REGISTRY_KEY] || createRegistry();
})(window);
