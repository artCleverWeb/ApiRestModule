$(document).ready(function () {
    $('.bar-a__select').on('change', function () {
        let $item = $(this).find(':selected');
        let $url = $item.data('url');
        console.log($item.data('url'));
        if ($url && $url.length > 0) {
            window.location.href = $url;
        }

    })
})