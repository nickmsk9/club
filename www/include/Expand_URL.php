<?php
declare(strict_types=1);
// Srinivas Tamada
// Version 1.0
function Expand_URL(string $url): string
{
    $returns = '';

    if ($url !== '') {
        // YouTube links
        if (preg_match('/youtu(?:\.be|(?:.*v=))/i', $url)) {
            // Extract video ID from query string or youtu.be path
            if (preg_match('/[?&]v=([^&]+)/', $url, $matches)) {
                $videoId = $matches[1];
            } elseif (preg_match('#youtu\.be/([^?&/]+)#i', $url, $matches)) {
                $videoId = $matches[1];
            } else {
                $videoId = '';
            }

            if ($videoId !== '') {
                $returns = sprintf(
                    '<iframe width="410" height="200" src="https://www.youtube.com/embed/%s" frameborder="0" allowfullscreen></iframe>',
                    htmlspecialchars($videoId, ENT_QUOTES)
                );
            }
        }
        // Vimeo links
        elseif (
            preg_match('/vimeo\.com/i', $url)
            && preg_match('#vimeo\.com/(?:video/)?(\d+)#i', $url, $matches)
        ) {
            $videoId = $matches[1];
            $returns = sprintf(
                '<iframe src="https://player.vimeo.com/video/%s?title=0&amp;byline=0&amp;portrait=0" width="410" height="200" frameborder="0" allowfullscreen></iframe>',
                htmlspecialchars($videoId, ENT_QUOTES)
            );
        }
    }

    return $returns;
}
?>