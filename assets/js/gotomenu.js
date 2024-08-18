(function ($) {
  $(document).ready(function () {
    function saveFocus() {
      $("body").addClass("gotomenu-open");
      $("body").attr("aria-hidden", "true");
    }

    function restoreFocus() {
      $("body").removeClass("gotomenu-open");
      $("body").removeAttr("aria-hidden");
      $("#gotomenu-autocomplete").val("");
      $("#gotomenu-suggestions").empty();
      $("#gotomenu-suggestions").removeClass("is-open")
    }

    function isUrl(str) {
      return str.startsWith("http://") || str.startsWith("https://");
    }

    $(document)
      .off("keydown")
      .on("keydown", function (e) {
      console.log('keydown');
      var isF2 = e.key === "F2" || e.which === 113 || e.keyCode === 113;
      var isEsc = e.key === "Escape" || e.which === 27 || e.keyCode === 27;
      console.log('isF2==', isF2);
      if (isF2) {
        // F2 key
        e.preventDefault();
        // Check if modal already exists
        if ($("#gotomenu-modal").length === 0) {
          // Build modal HTML securely
          var modalHtml =
            '<div id="gotomenu-modal">' +
            '<div class="gotomenu-overlay"></div>' +
            '<div class="gotomenu-content"><div class="gotomenu-inner"><h2>Go to menu:</h2>' +
            '<input type="text" name="gotomenulists" id="gotomenu-autocomplete" autocomplete="off" placeholder="Start typing to search...">' +
            '<ul id="gotomenu-suggestions" class="gotomenu-suggestions"></ul>' +
            '<button id="gotomenu-close" class="gotomenu-close">x</button>' +
            "</div></div>" +
            "</div>";
          // Append modal to body
          $("body").append(modalHtml);
        }

        // Initialize autocomplete functionality
        var selectedIndex = -1;
        $("#gotomenu-autocomplete").on("input", function () {
          var isActive =
            $(this).val().trim().length > 0
              ? $("#gotomenu-suggestions").addClass("is-open")
              : $("#gotomenu-suggestions").removeClass("is-open");
          var query = $(this).val().toLowerCase();

          let menuData = (typeof gotomenuData !== 'undefined' && gotomenuData.menus) ? gotomenuData.menus : [];

          var suggestions = menuData
            .filter(function (menu) {
              return menu.title.toLowerCase().includes(query);
            })
            .slice(0, 7); // Limit to top 5 results

          // Reset the selected index
          selectedIndex = -1;

          // Clear and populate suggestion list
          var suggestionList = $("#gotomenu-suggestions");
          suggestionList.empty();

          if (suggestions.length > 0) {
            $.each(suggestions, function (index, menu) {
              console.log(menu.icon);
              var menu_icon = (menu.icon && menu.icon  !== undefined) ? (isUrl(menu.icon) ? '<img class="icon" src="' + menu.icon + '" />'
                : '<span class="icon dashicons ' + menu.icon + '"></span>') : '';

                suggestionList.append(
                '<li><a data-url="' +
                  menu.url +
                  '" tabindex="0" href="' +
                  menu.url +
                  '">' +
                  menu_icon +
                  "" +
                  menu.title +
                  "</a></li>"
              );
            });
          } else {
            suggestionList.append(
              '<li class="no-results">Incorrect menu. Please refine your search.</li>'
            );
          }

          // Handle keyboard navigation in the suggestion list
          $("#gotomenu-autocomplete").on("keydown", function (e) {
            var suggestionItems = $("#gotomenu-suggestions li");
            if (suggestionItems.length > 0) {
              if (e.which === 40) {
                // Down arrow key
                e.preventDefault();
                if (selectedIndex < suggestionItems.length - 1) {
                  selectedIndex++;
                  suggestionItems.removeClass("selected");
                  $(suggestionItems[selectedIndex])
                    .addClass("selected")
                    .focus();
                }
              } else if (e.which === 38) {
                // Up arrow key
                e.preventDefault();
                if (selectedIndex > 0) {
                  selectedIndex--;
                  suggestionItems.removeClass("selected");
                  $(suggestionItems[selectedIndex])
                    .addClass("selected")
                    .focus();
                }
              } else if (e.which === 13) {
                // Enter key
                e.preventDefault();
                if (selectedIndex >= 0) {
                  window.location.href = $(suggestionItems[selectedIndex])
                    .find("a")
                    .data("url");
                }
              }
            }
          });
        });

        // Show modal
        $("#gotomenu-modal").show();
        $("#gotomenu-autocomplete").focus();
        $("body").addClass("gotomenu-open");
        // Save focus and restrict tabbing to the modal
        saveFocus();
      }

      if (isEsc) {
        $("#gotomenu-modal").hide();
        restoreFocus();
      }
    });

    $(document).on("click", "#gotomenu-close", function (e) {
      console.log('gotomenuClose jquery on click');
        e.preventDefault();
        $("#gotomenu-modal").hide();
        $("body").removeClass("gotomenu-open");
        restoreFocus();
    });

    $(document).on("click", ".gotomenu-overlay", function (e) {
        e.preventDefault();
        $("#gotomenu-modal").hide();
        $("body").removeClass("gotomenu-open");
        restoreFocus();
    });

    // Focus trap logic
    var firstFocusableElement = $("#gotomenu-autocomplete");
    var focusableElements = $("#gotomenu-modal")
      .find("input, button, li")
      .filter(":visible");
    var lastFocusableElement =
      focusableElements[focusableElements.length - 1];

    $("#gotomenu-modal").on("keydown", function (e) {
      var isTab = e.key === "Tab" || e.which === 9 || e.keyCode === 9;

      if (isTab) {
        if (e.shiftKey) {
          // Shift + Tab
          if (document.activeElement === firstFocusableElement[0]) {
            e.preventDefault();
            lastFocusableElement.focus();
          }
        } else {
          // Tab
          if (document.activeElement === lastFocusableElement) {
            e.preventDefault();
            firstFocusableElement.focus();
          }
        }
      }
    });
  });
})(jQuery);
