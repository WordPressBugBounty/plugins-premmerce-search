jQuery(document).ready(function ($) {

    // Config object is required
    if (typeof premmerceSearch === 'undefined') {
        return;
    }

    var searchSelector = '[id^="woocommerce-product-search-field-"]';

    if (typeof premmerceSearch.searchField !== 'undefined' && premmerceSearch.searchField) {
        searchSelector += ',' + premmerceSearch.searchField;
    }

    var search = $(searchSelector);

    var autocompleteItemTemplate = $('[data-autocomplete-template="item"]').clone();
    var autocompleteAllResultTemplate = $('[data-autocomplete-template="allResult"]').clone();


    if (premmerceSearch.forceProductSearch) {

        search.each(function () {
            var searchForm = $(this).parent('form');

            $(document).on('submit', searchForm, function () {
                var el = $('<input/>',
                    {'type': 'hidden', 'name': 'post_type', 'value': 'product'});

                searchForm.append(el);
            });
        });
    }


    search.each(function () {
        var search = $(this);

        search.autocomplete({
            source: function (name, response) {
                showSpinner(search);

                $.ajax( {
                    url: premmerceSearch.url,
                    headers: {"X-WP-Nonce": premmerceSearch.nonce},
                    dataType: 'json',
                    data: name,
                    success: function (data) {
                        hideSpinner();
                        response(data);
                    }
                });
            },
            messages: {
                noResults: '',
                results: function () {}
            },
            delay: 500,
            minLength: parseInt(premmerceSearch.minLength),
            open: function () {
                let form = $(this).closest('form');

                // Show all result handler
                $(autocompleteAllResultTemplate, '[data-autocomplete-show-all-result]').on('click', function (event) {
                    event.preventDefault();
                    form.submit();
                });

                $('.ui-autocomplete').css('width', search.css('width'));
                $('.ui-autocomplete').append(autocompleteAllResultTemplate);

            }
        });

        search.autocomplete('instance')._renderItem = function (ul, item) {

            ul.addClass('autocomplete autocomplete__frame');

            let li = autocompleteItemTemplate.clone();

            li.find('[data-autocomplete-product-name]').html(item.label);
            li.find('[data-autocomplete-product-price]').html(item.price);
            li.find('[data-autocomplete-product-link]').attr('href', item.link);
            li.find('[data-autocomplete-product-add-to-cart]').attr('href', `?add-to-cart=${item.id}`);

            if( ! item.isPurchasable ) {
                li.find('[data-autocomplete-product-add-to-cart]').css({'display':'none'});
            }

            if (item.image) {
                li.find('[data-autocomplete-product-img]').attr({'src': item.image, 'alt': item.label});
                li.find('[data-autocomplete-product-photo]').show();
            }

            return li.appendTo(ul);
        };
    });

    function showSpinner(field) {

        var fieldPosition = field.offset();
        var verticalPadding = (field.innerHeight() - field.height()) / 2;
        var size = field.height();

        var wrapper = $('<div>', {
            class: 'premmerce-search-spinner',
            css: {
                top: fieldPosition.top + verticalPadding,
                left: fieldPosition.left + field.outerWidth() - size - verticalPadding,
                width: size,
                height: size
            }
        });

        $('body').prepend(wrapper);
    }

    function hideSpinner() {
        $('.premmerce-search-spinner').remove();
    }
});