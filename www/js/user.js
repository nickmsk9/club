/**
 * user.js — tab-loading with cache bust and zebra striping
 */
(function($) {
    $(function() {
        var $body    = $('#user_tab_body'),
            $loading = $('#loading'),
            user     = $body.data('user') || $body.attr('data-user') || '';

        // Loading indicator HTML
        var loadingHtml = '<img src="pic/upload.gif" alt="Загрузка...">';

        // Apply zebra striping to table rows inside #user_tab_body
        function applyZebra() {
            $body.find('tr').css('backgroundColor', '');
            $body.find('tr:even').css('backgroundColor', '#EEEEEE');
        }

        // Bust image cache by appending timestamp
        function bustImages() {
            var ts = new Date().getTime();
            $body.find('img').each(function() {
                var $img = $(this),
                    src = $img.attr('src') || '',
                    sep;

                if (!src) {
                    return;
                }

                // Remove any existing cache-buster parameter
                src = src.replace(/([?&])_=\d+/, '');

                // Determine separator for new parameter
                sep = src.indexOf('?') > -1 ? '&' : '?';

                // Reapply src with new cache-buster, preserving other parameters
                $img.attr('src', src + sep + '_=' + ts);
            });
        }

        // Load a given tab by its act code
        function loadTab(act) {
            // Highlight active tab
            $('.tab').removeClass('active');
            $('.tab#' + act).addClass('active');

            if (!user) {
                $body.html('<p>Ошибка: не передан ID пользователя.</p>');
                return;
            }

            // Show loading spinner
            $loading.html(loadingHtml);

            // AJAX fetch
            $.ajax({
                url: 'user.php',
                type: 'POST',
                dataType: 'html',
                data: { user: user, act: act },
                cache: false,
                success: function(response) {
                    $body.html(response);
                    bustImages();
                    applyZebra();
                },
                error: function(xhr, status, error) {
                    var msg = 'Ошибка при загрузке данных.';

                    if (xhr && xhr.responseText) {
                        $body.html(xhr.responseText);
                    } else {
                        if (error) {
                            msg += ' ' + error;
                        }
                        $body.html('<p>' + msg + '</p>');
                    }
                },
                complete: function() {
                    $loading.empty();
                }
            });
        }

        // Bind click on tabs (jQuery 1.4.2 compatible)
        $('.tab').live('click', function(e) {
            e.preventDefault();
            var act = this.id;
            if (!$(this).hasClass('active')) {
                loadTab(act);
            }
        });

        // Initialize: load current or default tab
        var $tabs = $('.tab'),
            $init = $('.tab.active');

        if ($init.length) {
            loadTab($init.eq(0).attr('id'));
        } else if ($tabs.length) {
            loadTab($tabs.eq(0).attr('id'));
        }
    });
})(jQuery);
