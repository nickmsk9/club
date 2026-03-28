/**
 * user.js — tab-loading with cache bust and zebra striping
 */
(function($) {
    $(function() {
        var $tabs    = $('.tab'),
            $body    = $('#body'),
            $loading = $('#loading'),
            user     = $body.attr('user') || '';

        // Loading indicator HTML
        var loadingHtml = '<img src="pic/upload.gif" alt="Загрузка...">';

        // Apply zebra striping to table rows inside #body
        function applyZebra() {
            $body.find('tr').css('backgroundColor', '');
            $body.find('tr:even').css('backgroundColor', '#EEEEEE');
        }

        // Bust image cache by appending timestamp
        function bustImages() {
            var ts = Date.now();
            $body.find('img').each(function() {
                var $img = $(this);
                // Remove any existing cache-buster parameter
                var src = $img.attr('src').replace(/([?&])_=\d+/, '');
                // Determine separator for new parameter
                var sep = src.indexOf('?') > -1 ? '&' : '?';
                // Reapply src with new cache-buster, preserving other parameters
                $img.attr('src', src + sep + '_=' + ts);
            });
        }

        // Load a given tab by its act code
        function loadTab(act) {
            // Highlight active tab
            $tabs.removeClass('active');
            $tabs.filter('#' + act).addClass('active');

            // Show loading spinner
            $loading.html(loadingHtml);

            // AJAX fetch
            $.ajax({
                url: 'user.php',
                data: { user: user, c: act },
                cache: false,
                success: function(response) {
                    $body.html(response);
                    bustImages();
                    applyZebra();
                },
                error: function() {
                    $body.html('<p>Ошибка при загрузке данных.</p>');
                },
                complete: function() {
                    $loading.empty();
                }
            });
        }

        // Bind click on tabs
        $tabs.on('click', function(e) {
            e.preventDefault();
            var act = this.id;
            if (!$(this).hasClass('active')) {
                loadTab(act);
            }
        });

        // Initialize: load current or default tab
        var $init = $tabs.filter('.active');
        if ($init.length) {
            loadTab($init.attr('id'));
        } else if ($tabs.length) {
            loadTab($tabs.first().attr('id'));
        }
    });
})(jQuery);