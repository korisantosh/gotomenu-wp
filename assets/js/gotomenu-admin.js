console.log("gotomenu-admin.js loaded");
jQuery(document).ready(function($) {
    $(document).keydown(function(e) {
        var isF2 = e.key === 'F2' || e.which === 113 || e.keyCode === 113;
        var isEsc = e.key === 'Escape' || e.which === 27 || e.keyCode === 27;
        if (isF2) { // F2 key
            e.preventDefault();
            // Check if modal already exists
            if ($('#gotomenu-modal').length === 0) {
                // Build modal HTML securely
                var modalHtml = '<div id="gotomenu-modal">' +
                    '<div class="gotomenu-overlay"></div>' +
                    '<div class="gotomenu-content"><div class="gotomenu-inner"><h2>Go to menu:</h2>' +
                    '<input type="text" name="gotomenulists" id="gotomenu-autocomplete" autocomplete="off" placeholder="Start typing to search...">' +
                    '<ul id="gotomenu-suggestions" class="gotomenu-suggestions"></ul>' +
                    '<button id="gotomenu-close" class="gotomenu-close">x</button>' +
                    '</div></div>' +
                    '</div>';
                // Append modal to body
                $('body').append(modalHtml);

                // Initialize autocomplete functionality
                var selectedIndex = -1;
                $('#gotomenu-autocomplete').on('input', function() {
                    var isActive = $(this).val().trim().length > 0 ? $(this).addClass('is-active') : $(this).removeClass('is-active');
                    var query = $(this).val().toLowerCase();
                    var suggestions = gotomenuAdminData.menus.filter(function(menu) {
                        return menu.title.toLowerCase().includes(query);
                    }).slice(0, 5); // Limit to top 5 results

                    // Reset the selected index
                    selectedIndex = -1;

                    // Clear and populate suggestion list
                    var suggestionList = $('#gotomenu-suggestions');
                    suggestionList.empty();
                    ;
                    $.each(suggestions, function(index, menu) {
                        suggestionList.append('<li data-url="' + menu.url + '"><a tabindex="0" href="' + menu.url + '">' + menu.title + '</a></li>');
                    });

                    // Handle suggestion click
                    $('#gotomenu-suggestions li').off('click').on('click', function() {
                        window.location.href = $(this).data('url');
                    });
                });

                // Handle keyboard navigation in the suggestion list
                $('#gotomenu-autocomplete').on('keydown', function(e) {
                    var suggestionItems = $('#gotomenu-suggestions li');
                    if (suggestionItems.length > 0) {
                        if (e.which === 40) { // Down arrow key
                            e.preventDefault();
                            if (selectedIndex < suggestionItems.length - 1) {
                                selectedIndex++;
                                suggestionItems.removeClass('selected');
                                $(suggestionItems[selectedIndex]).addClass('selected').focus();
                            }
                        } else if (e.which === 38) { // Up arrow key
                            e.preventDefault();
                            if (selectedIndex > 0) {
                                selectedIndex--;
                                suggestionItems.removeClass('selected');
                                $(suggestionItems[selectedIndex]).addClass('selected').focus();
                            }
                        } else if (e.which === 13) { // Enter key
                            e.preventDefault();
                            if (selectedIndex >= 0) {
                                window.location.href = $(suggestionItems[selectedIndex]).data('url');
                            }
                        }
                    }
                });
            }
            // Handle Go button click
            $('#gotomenu-select').off('change').on('change', function() {
                var url = $(this).val();
                if (url) {
                    window.location.href = url;
                }
            });
            $('#gotomenu-close').off('click').on('click', function() {
                $('#gotomenu-modal').hide();
                $('body').removeClass('gotomenu-open');
                restoreFocus();
            });

            $('.gotomenu-overlay').off('click').on('click', function() {
                $('#gotomenu-modal').hide();
                $('body').removeClass('gotomenu-open');
                restoreFocus();
            });

            $(document).off('keydown').on('keydown', function(e) {
                if (isEsc) {
                    $('#gotomenu-modal').hide();
                    restoreFocus();
                }
            });

            // Focus trap logic
            var firstFocusableElement = $('#gotomenu-autocomplete');
            var focusableElements = $('#gotomenu-modal').find('input, button, li').filter(':visible');
            var lastFocusableElement = focusableElements[focusableElements.length - 1];

            $('#gotomenu-modal').on('keydown', function(e) {
                var isTab = e.key === 'Tab' || e.which === 9 || e.keyCode === 9;

                if (isTab) {
                    if (e.shiftKey) { // Shift + Tab
                        if (document.activeElement === firstFocusableElement[0]) {
                            e.preventDefault();
                            lastFocusableElement.focus();
                        }
                    } else { // Tab
                        if (document.activeElement === lastFocusableElement) {
                            e.preventDefault();
                            firstFocusableElement.focus();
                        }
                    }
                }
            });

            // Show modal
            $('#gotomenu-modal').show();
            $('#gotomenu-autocomplete').focus();
            $('body').addClass('gotomenu-open');
            // Save focus and restrict tabbing to the modal
            saveFocus();
        }

        function saveFocus() {
            $('body').addClass('focus-trap');
            $('body').attr('aria-hidden', 'true');
        }

        function restoreFocus() {
            $('body').removeClass('focus-trap');
            $('body').removeAttr('aria-hidden');
        }
    });
});
