/*
 * Tanzania Regional Referral Hospital HMS - shared behaviour.
 *
 * Loaded as an external script with a CSP nonce. All behaviour is attached
 * through data attributes and event delegation; no inline event handlers are
 * used (Part B). Pure deterministic UI wiring: no generative or "smart"
 * suggestions (Part D2).
 */
(function () {
  "use strict";

  function ready(fn) {
    if (document.readyState !== "loading") {
      fn();
    } else {
      document.addEventListener("DOMContentLoaded", fn);
    }
  }

  /* ------------------------------------------------------------------ */
  /* Mobile sidebar toggle                                               */
  /* ------------------------------------------------------------------ */
  ready(function () {
    var toggle = document.querySelector("[data-sidebar-toggle]");
    var sidebar = document.querySelector("[data-sidebar]");
    if (toggle && sidebar) {
      toggle.addEventListener("click", function () {
        sidebar.classList.toggle("open");
      });
      document.addEventListener("click", function (event) {
        if (sidebar.classList.contains("open") &&
            !sidebar.contains(event.target) &&
            !toggle.contains(event.target)) {
          sidebar.classList.remove("open");
        }
      });
    }
  });

  /* ------------------------------------------------------------------ */
  /* Autocomplete lookups (server-side, deterministic)                   */
  /* ------------------------------------------------------------------ */
  function initAutocomplete(input) {
    var endpoint = input.getAttribute("data-autocomplete");
    if (!endpoint) return;

    var wrap = document.createElement("div");
    wrap.className = "autocomplete-wrap";
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);

    var results = document.createElement("div");
    results.className = "autocomplete-results";
    results.style.display = "none";
    wrap.appendChild(results);

    var controller = null;
    var selectedLabel = "";

    function close() {
      results.style.display = "none";
      results.innerHTML = "";
    }

    input.addEventListener("blur", function () {
      // Delay so the click on a result registers first.
      setTimeout(close, 150);
    });

    function pick(item) {
      for (var key in item) {
        if (Object.prototype.hasOwnProperty.call(item, key)) {
          input.setAttribute("data-selected-" + key, String(item[key]));
        }
      }
      input.value = item.label !== undefined ? item.label : item.name;
      selectedLabel = input.value;
      close();
    }

    input.addEventListener("input", function () {
      var term = input.value.trim();
      if (term.length < 2) {
        close();
        return;
      }

      if (controller) controller.abort();
      controller = new AbortController();

      fetch(endpoint + "?q=" + encodeURIComponent(term), {
        signal: controller.signal,
        headers: { "Accept": "application/json" }
      })
        .then(function (response) { return response.json(); })
        .then(function (data) {
          if (!data.results || data.results.length === 0) {
            close();
            return;
          }
          results.innerHTML = "";
          data.results.forEach(function (item) {
            var button = document.createElement("button");
            button.type = "button";

            if (item.code !== undefined) {
              var code = document.createElement("span");
              code.className = "ac-code";
              code.textContent = item.code + " ";
              button.appendChild(code);
              button.appendChild(document.createTextNode(item.description));
            } else {
              button.textContent = item.name + (item.form ? " (" + item.form + ")" : "");
            }

            button.addEventListener("mousedown", function (event) {
              event.preventDefault();
              pick(item);
            });
            results.appendChild(button);
          });
          results.style.display = "block";
        })
        .catch(function () { close(); });
    });

    // Preserve empty state when the field is cleared after a pick.
    input.addEventListener("change", function () {
      if (input.value !== selectedLabel) {
        var attrs = input.getAttributeNames().filter(function (name) {
          return name.indexOf("data-selected-") === 0;
        });
        attrs.forEach(function (name) { input.removeAttribute(name); });
      }
    });
  }

  ready(function () {
    document.querySelectorAll("[data-autocomplete]").forEach(initAutocomplete);
  });

  /* ------------------------------------------------------------------ */
  /* Simple AJAX refresh for dashboard stats                             */
  /* ------------------------------------------------------------------ */
  ready(function () {
    var statEl = document.querySelector("[data-dashboard-stats]");
    var endpoint = statEl ? statEl.getAttribute("data-dashboard-stats") : null;
    if (!endpoint) return;
    setInterval(function () {
      fetch(endpoint, { headers: { "Accept": "application/json" } })
        .then(function (r) { return r.json(); })
        .catch(function () {});
    }, 60000);
  });

  /* ------------------------------------------------------------------ */
  /* Realtime refresh hooks (polling) for lists                           */
  /* ------------------------------------------------------------------ */
  ready(function () {
    document.querySelectorAll("[data-auto-refresh]").forEach(function (el) {
      var url = el.getAttribute("data-auto-refresh-url");
      var interval = parseInt(el.getAttribute("data-auto-refresh-interval") || "60000", 10);
      if (!url) return;
      setInterval(function () {
        fetch(url, { headers: { "Accept": "text/html" } })
          .then(function (r) { return r.text(); })
          .then(function (html) {
            var wrapper = document.createElement("div");
            wrapper.innerHTML = html;
            var current = el.querySelector("[data-auto-refresh-content]") || el;
            var nextContent = wrapper.querySelector("[data-auto-refresh-content]") || wrapper;
            if (nextContent.innerHTML !== current.innerHTML) {
              current.innerHTML = nextContent.innerHTML;
            }
          })
          .catch(function () {});
      }, interval);
    });
  });

  /* ------------------------------------------------------------------ */
  /* Repeatable rows (next of kin, diagnoses, orders)                    */
  /* ------------------------------------------------------------------ */
  function bindRowControls(container) {
    if (!container) return;
    container.addEventListener("click", function (event) {
      var row = event.target.closest("[data-row]");
      if (row && event.target.hasAttribute("data-remove-row")) {
        event.preventDefault();
        row.remove();
        emitDirty();
      }
    });
    container.addEventListener("change", emitDirty);
    container.addEventListener("input", emitDirty);
  }

  function emitDirty() {
    document.dispatchEvent(new CustomEvent("hms:dirty"));
  }

  function applyIndex(fragment, index) {
    fragment.querySelectorAll("[name], [id], [for]").forEach(function (el) {
      ["name", "id", "for"].forEach(function (attr) {
        var value = el.getAttribute(attr);
        if (value && value.indexOf("__INDEX__") !== -1) {
          el.setAttribute(attr, value.replace(/__INDEX__/g, String(index)));
        }
      });
    });
  }

  ready(function () {
    document.querySelectorAll("[data-row-scaffold]").forEach(function (scaffold) {
      var container = document.querySelector(scaffold.getAttribute("data-row-scaffold"));
      if (!container) return;

      bindRowControls(container);

      var addControl = document.querySelector(scaffold.getAttribute("data-add-row"));
      if (!addControl) return;

      addControl.addEventListener("click", function (event) {
        event.preventDefault();
        var template = document.getElementById(scaffold.getAttribute("data-row-template"));
        if (!template) return;
        var fragment = template.content.cloneNode(true);
        applyIndex(fragment, container.querySelectorAll("[data-row]").length);
        var rows = fragment.querySelectorAll("[data-autocomplete]");
        container.appendChild(fragment);
        rows.forEach(initAutocomplete);
        emitDirty();
      });
    });
  });

  /* ------------------------------------------------------------------ */
  /* Duplicate confirmation checkbox (register patient)                  */
  /* ------------------------------------------------------------------ */
  ready(function () {
    var duplicateForm = document.querySelector("[data-duplicate-form]");
    if (duplicateForm) {
      var input = duplicateForm.querySelector("input[name=confirm_duplicate]");
      var submit = duplicateForm.querySelector("button[type=submit]");
      if (input && submit) {
        function sync() { submit.disabled = !input.checked; }
        input.addEventListener("change", sync);
        sync();
      }
    }
  });
})();