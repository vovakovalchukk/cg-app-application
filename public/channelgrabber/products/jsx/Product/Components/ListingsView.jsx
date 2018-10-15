import React from 'react';
import ListingsRow from 'Product/Components/ListingsRow';


const listingColumnWidth = 100;// pixels

class ListingsViewComponent extends React.Component {
    static defaultProps = {
        variations: []
    };

    getHeaders = () => {
        var headers = [];
        for (var accountId in this.props.listingsPerAccount) {
            var account = this.props.accounts[accountId];
            var listings = this.props.listingsPerAccount[accountId].length;
            headers.push(<th title={account.displayName} style={{width: listings * listingColumnWidth}} colSpan={listings}>{account.channel}</th>);
        }
        return headers;
    };

    render() {
        var count = 0;
        return (
            <div className="listings-table">
                <table>
                    <thead>
                    <tr>
                        {this.getHeaders()}
                    </tr>
                    </thead>
                    <tbody>
                    {this.props.variations.map(function(variation) {
                        return <ListingsRow key={variation.id} listings={variation.listings} listingsPerAccount={this.props.listingsPerAccount} />
                    }.bind(this))}
                    </tbody>
                </table>
            </div>
        );
    }
}

export default ListingsViewComponent;

