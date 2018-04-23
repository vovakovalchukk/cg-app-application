define([
    'react',
    'Common/Components/Select',
], function(
    React,
    Select
) {
   "use strict";

    var SiteSelectComponent = React.createClass({
        getDefaultProps: function () {
            return {
                options: {}
            }
        },
        getSiteSelectOptions: function() {
           var options = [];
           for (var siteId in this.props.options) {
               options.push({
                   name: this.props.options[siteId],
                   value: siteId
               })
           }
           return options;
        },
        getSelectedSite: function (siteId) {
            return {
                name: siteId ? this.props.options[siteId] : '',
                value: siteId ? siteId : ''
            }
        },
        onSiteSelected: function(input, site) {
            input.onChange(site.value);
        },
        render: function() {
            return (<label>
                <span className={"inputbox-label"}>Site</span>
                <div className={"order-inputbox-holder"}>
                    <Select
                        autoSelectFirst={false}
                        options={this.getSiteSelectOptions()}
                        selectedOption={this.getSelectedSite.call(this, this.props.input.value)}
                        onOptionChange={this.onSiteSelected.bind(this, this.props.input)}
                        filterable={true}
                    />
                </div>
            </label>);
        }
    });

    return SiteSelectComponent;
});