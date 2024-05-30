$(document).ready(function () {
    $(document).on('change', '.bar-a__select', function () {
        let $item = $(this).find(':selected');
        let $url = $item.data('url');

        if ($url && $url.length > 0) {
            window.location.href = $url;
        }

    })
})