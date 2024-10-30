/*
 * Cnews plugin javascript.
 */
(function($) {

    function cnewsAccordion() {
        // Add accordion functionality to emails sent view.
        var $acc = $('.cnews-accordion');

        $acc.each(function(entry) {
            $(this).on('click', function(e) {
                e.preventDefault();

                $(this).toggleClass("cnews-active");
                $(this).nextAll('.cnews-panel').first().toggleClass("cnews-show");
            })
        });
    }


    $(document).ready(function() {
        cnewsAccordion();
    });

})(jQuery);

