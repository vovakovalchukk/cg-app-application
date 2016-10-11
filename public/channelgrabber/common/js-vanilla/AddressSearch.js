define([], function()
{
    function AddressSearch(selectorPrefix)
    {
        this.getSelectorPrefix = function()
        {
            return selectorPrefix;
        };


        var init = function()
        {
            this.listenForSearchSelection()
                .listenForManualEntryToggle()
        };
        init.call(this);
    }

    AddressSearch.SEARCH_INPUT_SUFFIX = '-address-search-input';
    AddressSearch.SEARCH_TOGGLE_SUFFIX = '-address-search-toggle-shadow';
    AddressSearch.ADDRESS_SECTION_SUFFIX = '-address-section';
    AddressSearch.SELECTOR_FORM = '#detailsForm form';

    AddressSearch.prototype.listenForSearchSelection = function()
    {
        var self = this;
        $('#'+this.getSelectorPrefix()+AddressSearch.SEARCH_INPUT_SUFFIX).on(this.getSelectorPrefix() +'-address-selected', function()
        {
            self.updateCountrySelect()
                .toggleAddressFields();
        });

        return this;
    };

    AddressSearch.prototype.listenForManualEntryToggle = function()
    {
        var self = this;
        $('#'+this.getSelectorPrefix()+AddressSearch.SEARCH_TOGGLE_SUFFIX).click(function()
        {
            self.toggleAddressFields();
        });

        if ($(AddressSearch.SELECTOR_FORM + ' input[name="address[addressLine1]"').val() &&
            $(AddressSearch.SELECTOR_FORM + ' input[name="address[postcode]"').val()
        ) {
            self.toggleAddressFields();
        }

        return this;
    };

    AddressSearch.prototype.toggleAddressFields = function()
    {
        if ($('#'+this.getSelectorPrefix()+AddressSearch.SEARCH_INPUT_SUFFIX).is(':visible')) {
            $('#'+this.getSelectorPrefix()+AddressSearch.SEARCH_INPUT_SUFFIX).closest('.order-inputbox-holder').hide();
            $('#'+this.getSelectorPrefix()+AddressSearch.ADDRESS_SECTION_SUFFIX).show();
            $('#'+this.getSelectorPrefix()+AddressSearch.SEARCH_TOGGLE_SUFFIX + ' .title').text('Search Address');
            $('#'+this.getSelectorPrefix()+AddressSearch.SEARCH_TOGGLE_SUFFIX).closest('label').find('.inputbox-label').text('Address');
        } else {
            $('#'+this.getSelectorPrefix()+AddressSearch.SEARCH_INPUT_SUFFIX).closest('.order-inputbox-holder').show();
            $('#'+this.getSelectorPrefix()+AddressSearch.ADDRESS_SECTION_SUFFIX).hide();
            $('#'+this.getSelectorPrefix()+AddressSearch.SEARCH_TOGGLE_SUFFIX + ' .title').text('Enter Manually');
            $('#'+this.getSelectorPrefix()+AddressSearch.SEARCH_TOGGLE_SUFFIX).closest('label').find('.inputbox-label').text('Search Address');
        }
    };

    AddressSearch.prototype.updateCountrySelect = function()
    {
        var country = $('input[name="'+this.getSelectorPrefix()+'AddressCountry"]').val();
        var countrySelect = $('div[data-element-name="'+this.getSelectorPrefix()+'AddressCountry"]');
        var countryOption = countrySelect.find('li[data-value="' + country + '"]');
        if (countryOption.length == 0) {
            // Have to use the ^= selector here to cope with things like 'France' -> 'France, French Republic'
            var countryOption = countrySelect.find('li[data-value^="' + country + '"]:first');
        }

        if (countryOption.hasClass('active')) {
            return this;
        }
        countryOption.click();
        countrySelect.removeClass('active');
        return this;
    };

    return AddressSearch;
});
