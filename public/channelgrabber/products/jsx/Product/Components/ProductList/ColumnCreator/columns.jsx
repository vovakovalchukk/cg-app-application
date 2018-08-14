define([
    'react',
], function(
    React,
) {
    "use strict";
    let coreColumns =  [
        {
            key: 'image',
            width: 100,
            fixed: true,
            headerText: 'Image'
        },
        {
            key: 'productExpand',
            width: 20,
            fixed: true,
            headerText: ''
        },
        {
            key: 'link',
            width: 100,
            fixed: true,
            headerText: 'Link'
        },
        {
            key: 'sku',
            width: 200,
            fixed: true,
            headerText: 'Sku'
        },
        {
            key: 'name',
            width: 200,
            fixed: true,
            headerText: 'Name'
        },
        {
            key: 'available',
            width: 100,
            fixed: true,
            headerText: 'Available'
        }]
        
        let listingColumns = [
        //todo - change this dummy data to something significant in TAC-165
        {
            key: 'dummyListingColumn1',
            width: 200,
            headerText: 'dummy listing col',
            fixed: false,
            tab:'listings'
        },
        {
            key: 'dummyListingColumn2',
            width: 200,
            headerText: 'dummy listing col',
            fixed: false,
            tab:'listings'
        },
        {
            key: 'dummyListingColumn3',
            width: 200,
            headerText: 'dummy listing col',
            fixed: false,
            tab:'listings',
        },
        {
            key: 'dummyListingColumn4',
            width: 200,
            headerText: 'dummy listing col',
            fixed: false,
            tab:'listings'
        },
        {
            key: 'dummyListingColumn5',
            width: 200,
            headerText: 'dummy listing col',
            fixed: false,
            tab:'listings'
        },
        {
            key: 'dummyListingColumn6',
            width: 200,
            headerText: 'dummy listing col',
            fixed: false,
            tab:'listings'
        },
        {
            key: 'dummyListingColumn7',
            width: 200,
            headerText: 'dummy listing col',
            fixed: false,
            tab:'listings'
        },
        {
            key: 'dummyListingColumn8',
            width: 200,
            headerText: 'dummy listing col',
            fixed: false,
            tab:'listings'
        }];
        // todo - change this dummy data to be something more significant from TAC-165 onwards
        let detailsColumns = Array(7).fill(0).map((column,index)=>{
            // return {
            //     key: 'dummyDetailsColumn'+index,
            //     width:200,
            //     headerText:'dummy details col '+index,
            //     fixed:false,
            //     tab:'details'
            // }
            return {
                key: 'dummyDetailsColumn'+(index+1),
                width: 200,
                headerText: 'dummy details col ' + (index+1),
                fixed: false,
                tab: 'details'
            }
        });
        console.log('detailsColumns: ', detailsColumns);
        
        
        let allColumns = coreColumns.concat(listingColumns, detailsColumns);
        
        console.log('allColumns: ', allColumns);
        
        
        return allColumns;
});
