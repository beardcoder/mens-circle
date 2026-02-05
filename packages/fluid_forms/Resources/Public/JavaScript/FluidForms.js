/**
 * FluidForms - Lightweight AJAX Form Handler for TYPO3
 *
 * Automatically intercepts forms with [data-fluid-form] and submits
 * them via fetch API. Handles validation errors, loading states,
 * success messages, and redirects.
 *
 * Zero dependencies. Pure Browser APIs.
 *
 * Usage:
 *   <form action="/my-action" method="post" data-fluid-form>
 *     <input name="email" data-rules="required|email" data-label="E-Mail" />
 *     <button type="submit">Send</button>
 *   </form>
 *
 * Data attributes:
 *   data-fluid-form          - Enables AJAX handling on a form
 *   data-rules="required|email"  - Client-side validation rules (optional)
 *   data-label="Field Name"  - Human-readable field label for errors
 *   data-success-message     - Override default success message
 *   data-reset-on-success    - Reset form after success (default: true)
 *   data-scroll-to-error     - Scroll to first error (default: true)
 *
 * @version 1.0.0
 */
(function () {
  'use strict';

  // ── Configuration ──────────────────────────────────────────────────
  const CSS_CLASSES = {
    fieldError: 'ff-field--error',
    errorMessage: 'ff-error-message',
    formLoading: 'ff-form--loading',
    toast: 'ff-toast',
    toastSuccess: 'ff-toast--success',
    toastError: 'ff-toast--error',
    toastWarning: 'ff-toast--warning',
    toastInfo: 'ff-toast--info',
    toastIcon: 'ff-toast__icon',
    toastContent: 'ff-toast__content',
    toastTitle: 'ff-toast__title',
    toastMessage: 'ff-toast__message',
    toastClose: 'ff-toast__close',
    toastContainer: 'ff-toast-container',
    submitButton: 'ff-submit',
  };

  const ICONS = {
    success: '\u2713',
    error: '\u2715',
    info: 'i',
    warning: '!',
  };

  const TITLES = {
    success: 'Erfolg',
    error: 'Fehler',
    info: 'Information',
    warning: 'Warnung',
  };

  // ── Toast Notification System ──────────────────────────────────────
  function getToastContainer() {
    var existing = document.querySelector('.' + CSS_CLASSES.toastContainer);
    if (existing) return existing;

    var container = document.createElement('div');
    container.className = CSS_CLASSES.toastContainer;
    container.setAttribute('aria-live', 'polite');
    container.setAttribute('aria-atomic', 'false');
    document.body.appendChild(container);
    return container;
  }

  function showToast(type, message, title) {
    var container = getToastContainer();

    var toast = document.createElement('div');
    toast.className = CSS_CLASSES.toast + ' ' + CSS_CLASSES['toast' + capitalize(type)];
    toast.setAttribute('role', 'alert');

    var icon = document.createElement('div');
    icon.className = CSS_CLASSES.toastIcon;
    icon.textContent = ICONS[type] || ICONS.info;
    icon.setAttribute('aria-hidden', 'true');

    var content = document.createElement('div');
    content.className = CSS_CLASSES.toastContent;

    var titleEl = document.createElement('div');
    titleEl.className = CSS_CLASSES.toastTitle;
    titleEl.textContent = title || TITLES[type] || '';

    var messageEl = document.createElement('div');
    messageEl.className = CSS_CLASSES.toastMessage;
    messageEl.textContent = message;

    var closeBtn = document.createElement('button');
    closeBtn.className = CSS_CLASSES.toastClose;
    closeBtn.type = 'button';
    closeBtn.setAttribute('aria-label', 'Schließen');
    closeBtn.textContent = '\u00D7';
    closeBtn.addEventListener('click', function () {
      dismissToast(toast);
    });

    content.appendChild(titleEl);
    content.appendChild(messageEl);
    toast.appendChild(icon);
    toast.appendChild(content);
    toast.appendChild(closeBtn);

    container.appendChild(toast);

    // Animate in
    requestAnimationFrame(function () {
      toast.classList.add('ff-toast--visible');
    });

    // Auto dismiss
    var timer = setTimeout(function () {
      dismissToast(toast);
    }, 6000);

    toast.addEventListener('mouseenter', function () {
      clearTimeout(timer);
    });

    toast.addEventListener('mouseleave', function () {
      timer = setTimeout(function () {
        dismissToast(toast);
      }, 3000);
    });
  }

  function dismissToast(toast) {
    toast.classList.remove('ff-toast--visible');
    toast.classList.add('ff-toast--leaving');
    toast.addEventListener('transitionend', function () {
      toast.remove();
    });
    // Fallback removal
    setTimeout(function () {
      if (toast.parentNode) toast.remove();
    }, 500);
  }

  // ── Client-side Validation ─────────────────────────────────────────
  function validateField(input) {
    var rules = (input.getAttribute('data-rules') || '').split('|').filter(Boolean);
    if (rules.length === 0) return [];

    var value = getFieldValue(input);
    var label = input.getAttribute('data-label') || humanize(input.name);
    var errors = [];

    for (var i = 0; i < rules.length; i++) {
      var parts = rules[i].split(':');
      var ruleName = parts[0];
      var param = parts[1] || null;
      var error = applyClientRule(ruleName, value, param, label);
      if (error) errors.push(error);
    }

    return errors;
  }

  function getFieldValue(input) {
    if (input.type === 'checkbox') {
      return input.checked ? 'on' : '';
    }
    if (input.type === 'radio') {
      var form = input.closest('form');
      var checked = form ? form.querySelector('input[name="' + input.name + '"]:checked') : null;
      return checked ? checked.value : '';
    }
    return (input.value || '').trim();
  }

  function applyClientRule(ruleName, value, param, label) {
    switch (ruleName) {
      case 'required':
        if (!value) return label + ' ist erforderlich.';
        break;
      case 'email':
        if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value))
          return 'Bitte gib eine gültige E-Mail-Adresse ein.';
        break;
      case 'minLength':
        if (value && value.length < parseInt(param, 10))
          return label + ' muss mindestens ' + param + ' Zeichen lang sein.';
        break;
      case 'maxLength':
        if (value && value.length > parseInt(param, 10))
          return label + ' darf maximal ' + param + ' Zeichen lang sein.';
        break;
      case 'phone':
        if (value && !/^\+?[\d\s\-()]{6,20}$/.test(value))
          return 'Bitte gib eine gültige Telefonnummer ein.';
        break;
      case 'numeric':
        if (value && isNaN(Number(value)))
          return label + ' muss eine Zahl sein.';
        break;
      case 'accepted':
        if (!value || value === '' || value === '0' || value === 'false')
          return label + ' muss akzeptiert werden.';
        break;
      case 'url':
        try {
          if (value) new URL(value);
        } catch (e) {
          return 'Bitte gib eine gültige URL ein.';
        }
        break;
      case 'min':
        if (value && Number(value) < Number(param))
          return label + ' muss mindestens ' + param + ' sein.';
        break;
      case 'max':
        if (value && Number(value) > Number(param))
          return label + ' darf maximal ' + param + ' sein.';
        break;
    }
    return null;
  }

  // ── Error Display ──────────────────────────────────────────────────
  function clearErrors(form) {
    var errorMessages = form.querySelectorAll('.' + CSS_CLASSES.errorMessage);
    for (var i = 0; i < errorMessages.length; i++) {
      errorMessages[i].remove();
    }
    var errorFields = form.querySelectorAll('.' + CSS_CLASSES.fieldError);
    for (var j = 0; j < errorFields.length; j++) {
      errorFields[j].classList.remove(CSS_CLASSES.fieldError);
    }
  }

  function displayFieldErrors(form, errors) {
    var firstErrorField = null;

    for (var field in errors) {
      if (!Object.prototype.hasOwnProperty.call(errors, field)) continue;

      var input = form.querySelector('[name="' + field + '"]');
      if (!input) continue;

      // Mark field as error
      var group = input.closest('.form-group') || input.parentElement;
      if (group) group.classList.add(CSS_CLASSES.fieldError);

      // Create error message element
      var messages = errors[field];
      var errorEl = document.createElement('div');
      errorEl.className = CSS_CLASSES.errorMessage;
      errorEl.setAttribute('role', 'alert');

      for (var i = 0; i < messages.length; i++) {
        var p = document.createElement('p');
        p.textContent = messages[i];
        errorEl.appendChild(p);
      }

      // Insert after input (or after input group)
      var insertAfter = input.type === 'checkbox' ? group : input;
      if (insertAfter && insertAfter.parentNode) {
        insertAfter.parentNode.insertBefore(errorEl, insertAfter.nextSibling);
      }

      if (!firstErrorField) firstErrorField = input;
    }

    // Scroll to first error
    if (firstErrorField && form.getAttribute('data-scroll-to-error') !== 'false') {
      firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
      firstErrorField.focus({ preventScroll: true });
    }
  }

  // ── Form Submission ────────────────────────────────────────────────
  function handleSubmit(form, event) {
    event.preventDefault();

    // Client-side validation
    clearErrors(form);
    var allErrors = {};
    var inputs = form.querySelectorAll('[data-rules]');
    var hasClientErrors = false;

    for (var i = 0; i < inputs.length; i++) {
      var fieldErrors = validateField(inputs[i]);
      if (fieldErrors.length > 0) {
        allErrors[inputs[i].name] = fieldErrors;
        hasClientErrors = true;
      }
    }

    if (hasClientErrors) {
      displayFieldErrors(form, allErrors);
      return;
    }

    // Gather form data
    var formData = new FormData(form);
    var data = {};
    formData.forEach(function (value, key) {
      data[key] = value;
    });

    // Handle checkboxes explicitly (unchecked ones are not in FormData)
    var checkboxes = form.querySelectorAll('input[type="checkbox"]');
    for (var c = 0; c < checkboxes.length; c++) {
      if (!checkboxes[c].checked) {
        data[checkboxes[c].name] = '';
      }
    }

    // Set loading state
    var submitBtn = form.querySelector('[type="submit"]');
    var originalText = submitBtn ? submitBtn.textContent : '';
    var loadingText = form.getAttribute('data-loading-text') || 'Wird gesendet...';

    form.classList.add(CSS_CLASSES.formLoading);
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = loadingText;
    }

    // Submit via fetch
    var action = form.getAttribute('action') || window.location.href;
    var method = (form.getAttribute('method') || 'POST').toUpperCase();

    var fetchOptions = {
      method: method,
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    };

    fetch(action, fetchOptions)
      .then(function (response) {
        return response.json().then(function (json) {
          return { status: response.status, data: json };
        });
      })
      .then(function (result) {
        var responseData = result.data;

        if (responseData.success) {
          // Success
          var successMessage =
            form.getAttribute('data-success-message') || responseData.message || 'Erfolgreich gesendet!';
          showToast('success', successMessage);

          // Reset form
          if (form.getAttribute('data-reset-on-success') !== 'false') {
            form.reset();
          }

          // Dispatch custom event
          form.dispatchEvent(
            new CustomEvent('ff:success', { detail: responseData, bubbles: true })
          );

          // Handle redirect
          if (responseData.redirect) {
            setTimeout(function () {
              window.location.href = responseData.redirect;
            }, 1000);
          }
        } else {
          // Server validation errors
          if (responseData.errors && Object.keys(responseData.errors).length > 0) {
            displayFieldErrors(form, responseData.errors);
          }

          showToast('error', responseData.message || 'Ein Fehler ist aufgetreten.');

          // Dispatch custom event
          form.dispatchEvent(
            new CustomEvent('ff:error', { detail: responseData, bubbles: true })
          );
        }
      })
      .catch(function (error) {
        showToast('error', 'Netzwerkfehler. Bitte überprüfe deine Verbindung und versuche es erneut.');
        form.dispatchEvent(
          new CustomEvent('ff:error', { detail: { message: error.message }, bubbles: true })
        );
      })
      .finally(function () {
        form.classList.remove(CSS_CLASSES.formLoading);
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        }
      });
  }

  // ── Live Validation on blur ────────────────────────────────────────
  function setupLiveValidation(form) {
    var inputs = form.querySelectorAll('[data-rules]');

    for (var i = 0; i < inputs.length; i++) {
      (function (input) {
        input.addEventListener('blur', function () {
          // Clear existing error for this field
          var group = input.closest('.form-group') || input.parentElement;
          if (group) {
            group.classList.remove(CSS_CLASSES.fieldError);
            var existing = group.querySelector('.' + CSS_CLASSES.errorMessage);
            if (existing) existing.remove();
          }

          var errors = validateField(input);
          if (errors.length > 0) {
            var errObj = {};
            errObj[input.name] = errors;
            displayFieldErrors(form, errObj);
          }
        });

        // Clear error on input
        input.addEventListener('input', function () {
          var group = input.closest('.form-group') || input.parentElement;
          if (group) {
            group.classList.remove(CSS_CLASSES.fieldError);
            var existing = group.querySelector('.' + CSS_CLASSES.errorMessage);
            if (existing) existing.remove();
          }
        });
      })(inputs[i]);
    }
  }

  // ── Initialization ─────────────────────────────────────────────────
  function init() {
    var forms = document.querySelectorAll('[data-fluid-form]');

    for (var i = 0; i < forms.length; i++) {
      (function (form) {
        form.addEventListener('submit', function (e) {
          handleSubmit(form, e);
        });

        setupLiveValidation(form);

        // Mark as initialized
        form.setAttribute('data-fluid-form-initialized', 'true');
      })(forms[i]);
    }
  }

  // ── Helpers ────────────────────────────────────────────────────────
  function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
  }

  function humanize(str) {
    return str
      .replace(/([a-z])([A-Z])/g, '$1 $2')
      .replace(/[_-]/g, ' ')
      .replace(/^\w/, function (c) {
        return c.toUpperCase();
      });
  }

  // ── Public API ─────────────────────────────────────────────────────
  window.FluidForms = {
    init: init,
    showToast: showToast,
    validate: function (form) {
      clearErrors(form);
      var allErrors = {};
      var inputs = form.querySelectorAll('[data-rules]');
      for (var i = 0; i < inputs.length; i++) {
        var fieldErrors = validateField(inputs[i]);
        if (fieldErrors.length > 0) {
          allErrors[inputs[i].name] = fieldErrors;
        }
      }
      if (Object.keys(allErrors).length > 0) {
        displayFieldErrors(form, allErrors);
        return false;
      }
      return true;
    },
  };

  // Auto-init on DOMContentLoaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Re-init on dynamic content (e.g., TYPO3 AJAX page changes)
  var observer = new MutationObserver(function (mutations) {
    for (var i = 0; i < mutations.length; i++) {
      var addedNodes = mutations[i].addedNodes;
      for (var j = 0; j < addedNodes.length; j++) {
        var node = addedNodes[j];
        if (node.nodeType === 1) {
          if (node.matches && node.matches('[data-fluid-form]:not([data-fluid-form-initialized])')) {
            node.addEventListener('submit', function (e) {
              handleSubmit(node, e);
            });
            setupLiveValidation(node);
            node.setAttribute('data-fluid-form-initialized', 'true');
          }
          // Check children
          var childForms = node.querySelectorAll
            ? node.querySelectorAll('[data-fluid-form]:not([data-fluid-form-initialized])')
            : [];
          for (var k = 0; k < childForms.length; k++) {
            (function (f) {
              f.addEventListener('submit', function (e) {
                handleSubmit(f, e);
              });
              setupLiveValidation(f);
              f.setAttribute('data-fluid-form-initialized', 'true');
            })(childForms[k]);
          }
        }
      }
    }
  });

  observer.observe(document.body, { childList: true, subtree: true });
})();
