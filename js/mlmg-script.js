jQuery(document).ready(function($) {
    $('#mlmg-category-filter').on('change', function() {
        var category = $(this).val();
        
        $.ajax({
            url: mlmg_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'mlmg_filter_gallery',
                category: category
            },
            success: function(response) {
                $('.mlmg-grid').html(response);
            }
        });
    });
});
