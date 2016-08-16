define([
    'react'
], function(
    React
) {
    "use strict";

    var VariationViewComponent = React.createClass({
        render: function () {
            return (
                <div className="variation-table">
                    <table>
                        <thead>
                        <tr>
                            <th>Image</th>
                            <th>SKU</th>
                            <th>chicken</th>
                            <th>daniel</th>
                            <th>Size</th>
                            <th>Variance</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr key="">
                        <td><img src="/cg-built/products/img/noproductsimage.png" height="23"/></td>
                        <td>0</td>
                        <td>little</td>
                        <td>kjui</td>
                        <td>Green</td>
                        <td>big</td>
                        </tr>
                        {/*{this.props.variations.map(function (listing) {*/}
                            {/*return (*/}
                                {/*<tr key="">*/}
                                    {/*<td><img src="/cg-built/products/img/noproductsimage.png" height="23"/></td>*/}
                                    {/*<td>0</td>*/}
                                    {/*<td>little</td>*/}
                                    {/*<td>kjui</td>*/}
                                    {/*<td>Green</td>*/}
                                    {/*<td>big</td>*/}
                                {/*</tr>*/}
                            {/*);*/}
                        {/*})}*/}
                        </tbody>
                    </table>
                </div>
            );
        }
    });
    return VariationViewComponent;
});