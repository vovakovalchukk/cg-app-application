import React, {useState} from 'react';
import {connect} from 'react-redux';
import {Field, FieldArray, FormSection, reduxForm, resetSection, submit, formValueSelector} from 'redux-form';
import Input from 'Common/Components/Input';
import TextArea from 'Common/Components/TextArea';
import Select from 'Common/Components/Select';
import ImagePicker from 'Common/Components/ImagePicker';
import Actions from './Actions/CreateListings/Actions';
import ChannelForms from './Components/ChannelForms';
import CategoryForms from './Components/CategoryForms';
import ProductIdentifiers from './Components/CreateListing/ProductIdentifiers';
import Dimensions from './Components/CreateListing/Dimensions';
import ProductPrice from './Components/CreateListing/ProductPrice';
import SubmissionTable from './Components/CreateListing/SubmissionTable';
import Validators from './Validators';
import ProductSearch from './ProductSearch/Component';
import SectionedContainer from 'Common/Components/SectionedContainer';
import SectionData from 'Common/SectionData';

const FormSelector = formValueSelector('createListing');


let dimensionsProps = {
    "variationsDataForProduct": [
        {
            "id": 11400132,
            "organisationUnitId": 10558,
            "sku": "EXRED",
            "name": "",
            "deleted": false,
            "parentProductId": 11400129,
            "attributeNames": [],
            "attributeValues": {
                "Colour": "Red"
            },
            "imageIds": [
                {
                    "id": 13812565,
                    "order": 0
                }
            ],
            "listingImageIds": [
                {
                    "id": 13812565,
                    "listingId": 10222599,
                    "order": 0
                }
            ],
            "taxRateIds": [],
            "cgCreationDate": "2019-05-03 09:28:10",
            "pickingLocations": [],
            "eTag": "2a4512ac55866b638c6d9749dacfa5f720e496d0",
            "images": [
                {
                    "id": 13812565,
                    "organisationUnitId": 10558,
                    "url": "https://channelgrabber.23.ekm.shop/ekmps/shops/channelgrabber/images/excalibur-stone-not-supplied-103-p.jpeg"
                }
            ],
            "listings": {
                "10222599": {
                    "id": 10222599,
                    "organisationUnitId": 10558,
                    "productIds": [
                        11400129,
                        11400132,
                        11400134,
                        11409247
                    ],
                    "externalId": "103",
                    "channel": "ekm",
                    "status": "active",
                    "name": "Excalibur (stone not supplied)",
                    "description": "Wielded by King Arthur!*<br /><br /><br /><br />* we think",
                    "price": "2.0000",
                    "cost": null,
                    "condition": "New",
                    "accountId": 3086,
                    "marketplace": "",
                    "productSkus": {
                        "11400129": "",
                        "11400132": "EXRED",
                        "11400134": "EXBLU",
                        "11409247": "EXWHI"
                    },
                    "replacedById": null,
                    "skuExternalIdMap": [],
                    "lastModified": null,
                    "url": "https://23.ekm.net/ekmps/shops/channelgrabber/index.asp?function=DISPLAYPRODUCT&productid=103",
                    "message": ""
                }
            },
            "listingsPerAccount": {
                "3086": [
                    10222599
                ]
            },
            "activeSalesAccounts": {
                "3243": {
                    "id": 3243,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "amazon",
                    "organisationUnitId": 10949,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Amazon EU",
                    "credentials": "7KcoJhcMl9cT5rTLVAL58CRe2hSr87TkfK9zdFb2urppxk7EPnDoisnizeXmEKMFeYlaBwVoYJG/J8rIB+MwSKJkisYeQ3Az/egy0uwOs3UE7Z/UunXuA5xMRIZLI0GkzHYH0DW/67U6/zrSNdNuJ+QXgQLiKIVp93xExHup3yGD0YLHvxNSw58/RoWrRLpL+TXpT6X9VHfigokGHrIu4uwRnyByUyQHaAyeUGBxItx/pbeldHE1T3LJoY8JiGkoQwIUYCiE4jqoLHCwXqzZ9pWQG5LFNiSaGkA7WmOmTsWZbymL7Mg/YA7DzyjuFXS1mfdDFCRN512M1fshjF+X4psCnutqb+k8UgBvEHGedBJtQrfecrFRmkovlWD0Po62JCzpJAGCKIQ6IZqioKZQLEhD4J5AMcmx+CiVHku52/5WvByALEWvk20Mu4nthBHMnrc8whDd1HNzL+Idj6r6PaebVX22E1mb8w7pUUcu20lA9LNwcBoT4VDHHXtO5nPwD4vsPAUykf5es2vuEHZfhXDN4kdJ2s0ZS85dqxK53Qc8IsI19Bifb0Z4ofOFhFApFJ+8Bn6/Dcm8KahJzH76F2cj2bSevXflDLtlbSzQjILyYP9UifJncZLriu6mHKv+b4oshBCzIl03MdWXgoLUrHhgOiGB1jctzTp92TYX0iQwomQDHudS56ZaGGDBMlJtED3F6ojBhNBZQy+vIeBTcbIBgs60H7PgAjbPACtt/nN4zlw/eOFQfUEyWW3g9lqGPaIGwZJFUZeFC1F+DmfyReyoOqOaHhe9HRYWxFVx/QDjTZReHTHtSFIsAVX7HH8lyH8XnJqVKqdIztJWmikcoyj4Djj/LtcOFlktZFRHtZZFZW9i8/gKWj5LuA0QhlwgteoF0cY8KNMqYhpdghRggN3mU66tiXl0hSHLbLsPSORRdJgi4esMT7jXzvFVXhllCqycx0EkrIbnz2HTndq9b6lAdDTxxLIEPcdObHnOAPLPWxlmrxzxxC6922DqkqlK9bmkKtrTHDuN1DrGP2p+Lf3mdrTGoskYwVJ2x89mR0QnnQtQM3jjXst/ujwZfRKtEOMNx1Cwq3uqOSsCTt434A==",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "sales",
                        "shipping"
                    ],
                    "cgCreationDate": "2017-07-11 10:47:32",
                    "stockManagement": 0,
                    "externalData": {
                        "fbaOrderImport": 0,
                        "hash": "3e474f1d119d973829736ed9f7e18a699ff607bf",
                        "originalEmailAddress": "",
                        "fulfillmentLatency": 2,
                        "mcfEnabled": 1,
                        "messagingSetUp": 0,
                        "includeFbaStock": 0,
                        "stockFromFbaLocationId": 2796,
                        "regionCode": null,
                        "marketplaceIds": "A13V1IB3VIYZZH,A1F83G8C2ARO7P,A1PA6795UKMFR9,A1RKKUPIHCS9HS,A1ZFFQZ3HTUKT9,A38D8NSA03LJTC,A62U237T8HV6N,AFQLKURYRPEL8,APJ6JRA9NG5V4,AZMDEXL2RVFNN"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1563850808,
                        "processed": 49,
                        "total": 45,
                        "lastCompletedDate": "2019-07-23 03:36:47"
                    }
                },
                "12354": {
                    "id": 12354,
                    "externalId": "47fwg8cpdt",
                    "application": "OrderHub",
                    "channel": "big-commerce",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "BigCommerce",
                    "credentials": "nYg4OGzKeMmDeVdlbKJRBg3WLTlxIWOK0or+WcNloytYBe91o1iu4DDfhTuCiat72+b+3G8yCA32SCXdB2+mzBxnW2mkSfUq73gHOKhQpNkBiA5dT+XYfHokcdHJErxWps+QtofTX0zFmINbvZOvmAvPpLdUblNMW6DI7IJ2wHoGFlo0UjB4b1PIZJ3HbgDZRD3RQWCIHbXgxpuOAKAJx6dHVtnx6hxqVGMrXB2CApU=",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": "2018-07-30 15:12:37",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2018-02-19 11:20:51",
                    "stockManagement": 0,
                    "externalData": {
                        "secureUrl": "https://store-47fwg8cpdt.mybigcommerce.com",
                        "weightUnits": "kg",
                        "dimensionUnits": "Centimeters"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1521127671,
                        "processed": 13,
                        "total": 13,
                        "lastCompletedDate": "2018-03-15 15:28:15"
                    }
                }
            },
            "accounts": {
                "844": {
                    "id": 844,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "ebay",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Calico Trading",
                    "credentials": "iCpnvOwePsMJq7J40bqlO77erZ5X+00dzKvuRk4PpSGCEsEYCixvrALXTh8lZ4anCsePIJMLRFc8MN0C2DNL7WWBffE20qfU4ZmfY6BtJjLVpXn3Y8/aLas6uI7BYX+xydtAavkSmiGJOLPEtQZqCpHT008zPFTA59ebB4tDe2DvZHIZAPoxMX+QfpaaujyBxpzw3RGmId4C6LzUJ2G5meV8tzw92/SMU5alnWCrX+p1LUK3tk7CJRFDU6PSOn8Lh8ZegQEAoMUGMEOCZuIvhopDmYiCm2PLvk1f+IofZXTufQtAjZBS5yyDTVqqKSS056zp02tyh3J0aATDFpVONkJ3IaTFRNpH0eG3nwwsI0RgaRPTNVr/c2Nhf/KblTE0P8iOus8UJZTIesgXQApt2yvUr/P/X/VD0gkXZO/nREmdRqAerC1Usx5mCLvAUBYoNo3el8jsdYFX2ykzbwFd0cHJGaQPujEdjmR4ELs/llTelUGT6v+MIrfw9cZQ8SrP2OziAP5lsrr9tqi9xG45dGas+/jCOWdU8eAxm5rcQEtDlWG1Kk74tbwWFLqMgrKIVE+yx5Xtud+cKgEp2IDD+4bc/7plEJBW0XQ6nMJPelfKq4DnQe4vw0hcgyJjAzJFyDQtN0xVlOmciVHRi44PTgEFKTVUmwBzwvxsNeUR1an5qeZ67gOxRHgndI0QVq3aKI8vm8+1arW1Hg7iYYbdoZ0L+Inl+SGRdQFVwfvgmLjV9YacJV4o/m2X/RUawj7i386r1HSitafwnICDgsOk/psvSb7phj4Z/2jxx+E5VjlW38v6bVpk6UYuGimbVyh9gqNGq3oX1rRPG7jAiUQTGIoSFt56BJFAEyDMXqNnzv3c/nYm+gTw40pmhPPAUMb30ZMecHdIG5ulqgaQaxADOM3Lc4VddBhFO9ejdIrACf+Az+TI4dzGgDnh/62yrS6hkdP5qR3N1LUQmyEgDH386oy7uQsoe57Dnuy29YNI9ijjC/3Zlf0k/O0SzqFCKGDOTOWPDA8yj5bw4ZnwyWE0Sl7FF3QshrhdmMlJ8hZz6oE8M3J8ynNPNzHl4k+ItplWSp+tnOgMv8r6CZ3/jvW1vfUQ1z2kzw7g8dt6NcQjFTbCAriDdhJPYTgeOtDRwaWpHuFrezA0suhYNVo/7CUyGzkOk1XFpMazNlBUKDFHFwGAHLMRLTKivg0r/8pQzoqROxUjDedGs8YXQNRAkQzdQx2cTEwW4yJNrEn9j8nFD+84l5j+xKTQfwkbfQ0AzVBO/psTYA4PAZDArtxqxiTroiMNdaZ3P8vXDpojkardR2QKsQEEoInXaGHpNzxLVdnrZcbRBCZMaWacecUH6H7vE41PAnslbm6E/0h1gCHK2tqYCLH1M/iYTL/hp64nPlPyCb3P0/TGu/gFcamxSRqPF4cP/MnENAtgIW9UxRsEEUbMVSvYxg9MtkADggF9pmL2L4Crkj+FbTZ7+yhRxhU2ycwbhZzoEXDOqPauxnDEXIbXlV0gJrUnhwIcA0NQi5JkyZukM3HjvWX4j/MB1mFsKlA0wdfVYmh8kIFr6bLCfjuipbC/sUIB/93U+rvSGiaVNqM52w6dJjIQZ+p9eDJzKyHy5JIipPRhCcMpBx5xnUA9rlwhOhy9wKzxRfUQApXOPu2MavivSO/8cP5mLdkylbH3T1vBBcuSVcHhQ+Wvhpd4R1zIAt8EtZyfSJgsiw3EsQHXfebAoKffXQNKX63T2bXJi4WAOrRYjPAsey+YmHk=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": "2017-07-20 12:16:35",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2016-01-27 12:16:36",
                    "stockManagement": 0,
                    "externalData": {
                        "importEbayEmails": 0,
                        "globalShippingProgram": 0,
                        "listingLocation": null,
                        "listingCurrency": null,
                        "paypalEmail": null,
                        "listingDuration": null,
                        "listingDispatchTime": null,
                        "listingPaymentMethods": [],
                        "oAuthExpiryDate": null
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": true,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    },
                    "listingsAuthActive": false,
                    "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=ODQ0",
                    "siteId": 3
                },
                "1096": {
                    "id": 1096,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "royal-mail",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Royal Mail PPI",
                    "credentials": "Royal Mail",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-05-25 09:55:08",
                    "stockManagement": 0,
                    "externalData": {
                        "PPINumber": "HQ12345"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1445": {
                    "id": 1445,
                    "externalId": "1445",
                    "application": "OrderHub",
                    "channel": "parcelforce-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Parcelforce",
                    "credentials": "2+p/GVQs1ndg/heKwGT6bePmrr9ElapPzIhmSYdggFDPypxFY+/sIyYl5nWNhBpugPdB/rWFnyon41Trir9I1tPLadwkG3tx4nXqeN1Fs417/NKHRZtZw2pYcLAOYiJO5egBD/wtYAVOWwTie99HiBsOXxjuOifLQ3/eoo2lgorjmnQeRJ5sKY535YOsHS3m0F47C2ypo5emUIw3pXCoSncxdDydOmrY0H5tJLUIA9nGZ7DDuNBQyfFuu97XsIExuriMw3qIg9MXPcAFy56silpxXdE8qMAlIN9NNJQqlcSOt++u6XpoeO6FEHXmvc/186H3Pi/XXwp/xpr7+0Y8FK6K0/rPga17hGWRLY+AidVnNyYl7qc1LljcEmhSXD58fpzMIOcH6XRjiV/giHHZ4EqTKBMIBpxwJ8fpqpJAGAlGs7t05vol/44LQ37cVzNp",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-08-30 14:27:11",
                    "stockManagement": 0,
                    "externalData": {
                        "config": "{\"emailNotification\":\"1\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"1\",\"sms_pre_delivery\":\"0\"}"
                    },
                    "displayChannel": "Parcelforce",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1447": {
                    "id": 1447,
                    "externalId": "1447",
                    "application": "OrderHub",
                    "channel": "dpd-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "DPD",
                    "credentials": "ljkIEIyzleoeSE6GrLzXJXh9nlRkVmiWu+tEm023Qsld2iu0461qm3LK9ZmwxZ110Jh/PBp8E1hUuUd61B/7cei8QWZcF8qjAq6IyZnkL+MygqJrScSdbowuiFSJfsw2oKiNH5pkLZ37HMyi/s4bNkCTOCzNIF+QBeWDX7GEXwXAkBhMGUIrQcXrjvf/aJV6+9D2Wv3TZqXRrZHg8HYqL7KJm1f9FGQ5H6Fxsn5Ams7+qTcTfV4nxKB7mM2aQxLbPF2rz0B5UU4kKQgLjc6p6ISTm+HRkEPqo+TQMZU9diBQOlrEm5MPBDK/y/QKZf8SqtEG7L3VKSw5pbpyThRUvcEeWsq5eW+r3zQ1bhzOewYHHD3psQWUlWlWC2+ERO430xrYDiihs5gOBhtG5rYI15g5Hz7GrRSPXTJl2KHeOrwTUnKVdmgOTYFBNwiXB9yHAMw79394xLhEpgeoZAon59z+n/kgCV+xf3164Up2DNB4ZXeC0bKCwZS5UU1aqGV8imcBrsh45MlaF/jDeRI+ZoWhOUjGdJZrqibPhAKnOG0PW4028tQ7WUwl1Q8qZ10AQRqQMTIChoiTVr/CYJ+P+fW0redHDDXzi2jSa4sp9sPnsmkCIP0wuOkZU3yxawpi",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-08-31 10:08:30",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "DPD",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1448": {
                    "id": 1448,
                    "externalId": "1448",
                    "application": "OrderHub",
                    "channel": "interlink-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Interlink",
                    "credentials": "NhqQH5Yvo2mTPMAitLN8XOZRGJjNKbu4ld0zvfLH195fntGbuiTG++69OzEB8uB0xUkNuWl26t4ou+Xl3A3pG8Nj4doKnuE7Tnztrn82wVrGMkJHElVGs06ffZdFvG0s2MKehldhazxC4ycuEbjDX/AQZkOTULoat+XeDeujvZnN2xxB+o7xpx5FOjeJTyyypjoFa8MEtGQovHpCPYO7ph/Av7MU15q9doddvrARtiIEK987xXMSnei8Q+eauGWUs+74javCusSj0u5QKPLOoph/iUNtsU6XPuMgSbUvaNpQBIR4HVo/ztnXlOx8JeNC+TrnBQX13N+5I267uYhMNpZyh3I4jj2IE44WeJvWCCBCw+68U3UR4DMccBKx5ClJkReekIwl9D9KNO+dK1lEjL62B9peb1JQ+RgFeulo1XB4otF/cJXD9MeliZVDF8P2rR3v7QhyHfpMPQdOu8w2/blnjpu1PUdUPQhUVACqPNJjYpLLSeSWZjZaTENJs/lTTmOSUAMYMsVwCNAWQL8zpgxNvK3PmtStI9g4uNhRPUUgt1d+L+Pu/wSqkVhqQ24YbahGfPHKCC09QODqBBkgaHk0IlcVIsKLOJ5efJBCP79HOpeN5ZvZpBhhni+yAhDxeRlk996cQJGl85xiVHGgU6Tf1KycBa+SWeKj+y90s1aKVU5yLhEJL+DNeq4vXHWMt5KvQoA2si8GMUoKzDnP1w==",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-08-31 14:28:29",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "Interlink",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1456": {
                    "id": 1456,
                    "externalId": "1456",
                    "application": "OrderHub",
                    "channel": "parcelforce-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Parcelforce",
                    "credentials": "X5d7L4B6DUXntVIsKEC6J7ULjviSYN9GsxICofFbFW6PswrPlEmAdeK7IU7ZjFrFRTPaP7W6e/Iz+jG+KqKQNCLVF+B2ggau5v22zwx4KGTl1+9TYhkfhGHXhA95m2l5sVBSNOiSr9ly/kprrBXa7l22ouWiXYXt2Fzcx2VbDjYd4zAIN1Tp5N80alyfkRzVM/RoQJ9IwFVoFHqMXE2FVPUz5VAriZ9LM5DTJHUYuS2bZ8d+s8c4BOzrwi7NZhEzzsbWtDF9gKNRKc/wqKW3idSMPGvSJTnjCGMH9+7FxHXhYN9BE/igqnluhIxUHttJ7A4FQw3yEypyDDybfJzta54pGULumsMmqkBSOZ69YgKCrYpgxfZdhfnzmy8hIiAwoTOZVsgQbBP4rcbFyyD/O+pXGuVh3IDeclenPbv3i0jMu0SsVFDwI5QcDoostNQMbhCe/+nuTvREI1p86aJyAA==",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-01 11:32:39",
                    "stockManagement": 0,
                    "externalData": {
                        "config": "{\"emailNotification\":\"1\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"1\",\"sms_pre_delivery\":\"0\"}"
                    },
                    "displayChannel": "Parcelforce",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1457": {
                    "id": 1457,
                    "externalId": "1457",
                    "application": "OrderHub",
                    "channel": "dpd-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "DPD",
                    "credentials": "fG7mb7o2273LKUcn60vpLqfgxMp0G7LArSWNJBhjacburSEe4dIIXIwv33UPngs02UrfSOtf6YxYLQ6+efXNa6NbF4r1WlcVlbsQq4kBrkPtHWnfJE/IUgj6FNC0p4vqMB/3bwaV6f/gJgkSeTMmTGnRtr36icakeFbgOG+n4mBJhMpH+CMErlhJnO3+7Kq7PoAaA/1EZyHSf5hMBnrU4ZBrFEaGChToDRaiZGPgAiFWs02BlzVXAFLQou3FD+UauH+zbW1kRXCd+OOYTG/ew4yPNPB8SC3CCHwci5QiESVIs+q/qCApLMBVPVq6/EA8bghNsO7VllIRhUqNaHC/X+K9IePaplS38FV7nNd8twLayj0Fv7JSNqD8BwgVWM+p5geadxX9T05fQ5ijqfCP3qablNY1hJWDQMnxbvhExxjSO0BPvaafYOHE/HiokdsCDjLiiBCa4q48O/tiLMgaR0kjpFmD8xcmZj5+fPKTCXKd6jssI9pTEtoon9dQhCo0S/kF174ke7r6vj/9lKr2rTdVGlhNoqhhxNet3AeXppMk7PZ2JxpiYFQIy3CTuCs6Cce4c3Gdn1Ws/iSZi/9PpMhP/hvUxYDO6SMN5AmI7S0=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-01 11:51:43",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "DPD",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1458": {
                    "id": 1458,
                    "externalId": "1458",
                    "application": "OrderHub",
                    "channel": "interlink-ca",
                    "organisationUnitId": 10949,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Interlink",
                    "credentials": "xt5MOjdt8njG8ACdxh7Bj0wTCGxMqU0wJXIoG8bM/JuUSSpCM7P+/P7OhAjROM1jnbeSBpT9UDmfgc23CaR2kW/ebcVqrRMwWsxDoC0yzR/adLgTn+TnV7JqGWYV2Te06IV9otvmWG30mOSvrawVTXM956dun/Al/hUAM2E8CJFFnG9nG11DKXfa7CB9X9PeCGGHq+YRuK/n7xI+s7WblT+BU1YSIyhGiSvzKCYIrNtNwDjq7m8RqDsCtYNGAUAufF2pACZKU5L/YF7ClH+5pzwAFalqepI6GjrnMkO5gIMHA1qpoiYBDlSdctRGIeteCz52n4vvlwHAhWQKX/URUiRm8JYUdwCKcRvKw7SuYm4DN4nEnjA8oVNOA0zvMMtapgvuHQDX10NJ3Zwahh8BLQo2XvjFfi8uHkJRYP4OqbCULWard/8jBosdZOPtXJFXF0ZGXuxQEm/vjNOfz2wOlhXAK8ppZsm3YV3xDv8cIglkWExxs9z20i0IBQYjON6xmJLymqwqBmWFo9AK8KPZ15pmOoWHOgAcfpKduqUbNoTNEfnLkDz+eYnpXvWRj4jy5myjKUoi/QaLBBBK0G+bH+61cAgsW8bwaI4Wl1+Hqc8OlWc7a+mrmPpSNj7291kr3zO8oM+C1SASiXjo0oqTfiGiC9jeMSsLRiTz70gLn83dqaLkwbtvFsI9z1JPVA27",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-01 12:01:39",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "Interlink",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1517": {
                    "id": 1517,
                    "externalId": "1517",
                    "application": "OrderHub",
                    "channel": "myhermes-ca",
                    "organisationUnitId": 10949,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "MyHermes",
                    "credentials": "h1S6HNiK87jLSSQ9mQdgnFR3kwFgymBbpYfKOKTOqA6W2GDiHqs4GRc8HFPlDPA4quy31sQY+v7aVieuGjrewV0mV7rExhiVhpnPE2e6YOr1OVk2FS3VVfVSKKhcMy4RVBppxlE1hPgW+Mwe8WHtW10AFemp62BcTQNXsSIzfMwNJVjYpm3yZklFUMWUiUMqJAsyi7QkZUKVOY/z36k0FVgYPMjeq+WdaUm4T8jvmVXJtLJheQjpiYD8C8vFutCMC5JwCEAOJp0EPUiniz+FhkOI7b3s1U0wUt8R/aI4VD+R4JnHhohsCJHfupcz9xMQbc+3FeociXqZJJ8JLHZSRHE1g9FcBCzHHAMaFT8z3Tc=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-19 14:43:13",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "MyHermes",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1518": {
                    "id": 1518,
                    "externalId": "1518",
                    "application": "OrderHub",
                    "channel": "myhermes-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "MyHermes",
                    "credentials": "KhhFj5g00yulMijSIH+AIhJDZOKhkCOvK8bmDmK5ZiL08EdLU1JvIkoZdb+/nLDwuv78t6Mkh7zjHnj2QyrDzVBYmYp1gIP2otSCu84PvwFEOgRIEGKXIp18kwYHMBkhE0HryaoBVwYnqORH5/vhVz2rmUl3q33+6F9oeKIEGziK5vqf8TjDXJklCGCahkQe+zjY1cPzQc43pLaTI8meQ6i5Fc2NtMglKrStfE3sysmOH8Qw0aNHzDs0R6egbZvvxbvcYDl3bqk6qpllOE6dqUTYu5OSkYXN5ckY2BzyuyjgpF5Qbt0ytCFp5WhngpdcsAzPBSJsbxLi45+KvUcnBCrtlxCbS+0kzxyj470rTR4=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-19 14:48:47",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "MyHermes",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1519": {
                    "id": 1519,
                    "externalId": "1519",
                    "application": "OrderHub",
                    "channel": "myhermes-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "MyHermes",
                    "credentials": "9ss6kvrwHKcLDKIZomeQeosQVceVYY83oaK2gEN9EWvxyZBgc36LXjeFXjzCqNdZgPJova1NC8lsOmxLEQ6rQkqJQ2ORcwP61RON3qtTVmOtbRodGWI8F6Tif5l4JVwN3V6U0eYv0HeJIAZS5P+y+qiEWNteN3oMI5s6O2Z5ROpFJm4Wmtr+mWhJstqHXxzfVowEr0jgzVV9ovv/I3ovHrm2oR18pJpQ8F9hmbKlWS8Mx/tsuprfKDXHB6yY0TtY1A9rVP/yVR6idBExH0WovwBSiWH/w55ZoqTdrNMvlnIIdQ0VBFezjqlg26DQQsvfnbWToT771KD0iskq+2HTY2vwjQndkoFFQXO0aX0Gr9E=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-19 14:49:22",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "MyHermes",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1520": {
                    "id": 1520,
                    "externalId": "1520",
                    "application": "OrderHub",
                    "channel": "myhermes-ca",
                    "organisationUnitId": 10949,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "MyHermes",
                    "credentials": "CktJet0qBgjEGaDOHPUDWB6KCpZAtwnby4eUv3mGBDbA/xa91TRzfWZp1W4R16wOwB/A5EXvYZMpxQZg4XtdRDnfapKH+QpL6zD3ppFnXlwU51dBEq/X3ulpR2VUSPoxzaaKsFW7BsopLwlNMkBg6XKPiy/VBawcdGocWFgZUEuptaBhywgJaX34BV5ozh3aECMrB7P7zHfG2awMDizXerCg2zjeiSr4oTiL1ohbMMMYoA+dr5JIWrCpk+KIUSymEkjgeHS1eOSSr/XqoaZ8RrB45XVYFzIlOXEsGydlGA3VTVhCNE6E6AsLmO0pWopCSx0aQDc7oUk04KDDFaGSg/i3aFMIxxL2s1RzO+ucQ0s=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-19 14:53:24",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "MyHermes",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1822": {
                    "id": 1822,
                    "externalId": "1822",
                    "application": "OrderHub",
                    "channel": "parcelforce-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Parcelforce",
                    "credentials": "S4gu60+S+paiPJMW7SRwvl1pT6QI2trrbjLFXSU0W52/RtzGLT10+TbGnh65r1EL8/imaGzb67YQgbU/zDCE3v763VAP7gKfrq6ifPlHzluRaXQteGpmKzQQWPp39q6XgTzzAANLI2otAzTrQjZXYY9fUCaYdGyR2QmxOdhfZlbJQBq7cmvOHk08fPy+3DYc9sIGnOXLLpdS1rJ2apJWY03oS6d9DLwXRvfKPrwHW8mofDFl+WK4gZyRUcrlLTp2v2HrzDw9TPkqs7chL+COpbscgph4soytOYCrl/Tq2gAqjVjoC4xaUCzrbZ1RY8U/GpxFcwvJW0Gi6ZgU+4UEYLFeGa5He61pExi+bmwp2Wbase4DJjfipO4anqqwySM4iC/xjKJMg7mD8CLWoLHqzsYw4ZvrM7tg4pQo8tBVDlidp7S+DVg6nDMgogppJ4XbOLW+/62n18TvD1DJNSGLDQ==",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-11-16 15:41:22",
                    "stockManagement": 0,
                    "externalData": {
                        "config": "{\"emailNotification\":\"0\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"0\",\"sms_pre_delivery\":\"0\"}"
                    },
                    "displayChannel": "Parcelforce",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1823": {
                    "id": 1823,
                    "externalId": "2015291000",
                    "application": "OrderHub",
                    "channel": "royal-mail-nd",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "CG OBA",
                    "credentials": "hCrj92WJJBP/ZwLM2YAsOUQpIJgNNHR1miGevkZ+ojdpUQaP02BEdQnku/fS+aZPgK+iCw4eoJuAD+2qY+FuIL3BC2jlfZCvlJ77R+bke8Mpp3/iUSFCenaWyxSbCpId4AsgilA75jx6vp9iJb/JfJ4E4ptVX+xXKkbasftz6zahmX9ZPGyQ5xF5EuPoT5jIPi+1Nrn7NwczmdUgFXYELPjeVCV3Mu4+Fktfd5u15sL0IN8N221wSo/iXsdEb/JkXxwiyyNaUJpplsdrRTF0tMRaHj5iz8NbxjzL/q7DZh5E8zHHGcCbWoQ5ZdRaUWEA8W6qF3Snxk/Q7KgHdmFYPMl82/MFAuqVgJN8JDVKSGpoxdB6Hew4iC1cibJOJYJbyTS/j8VBiAOex5jLRjwGpiX8cK+7tsWRdhcP27uX8SgZqvvBImyH9kp901/V5HYFiDGVJtd8j8zNpTVngEJ9szBrFVKQSrOvob4ZBCOLASpNP47CrYYmcYXcuO0hy1jGvSL7FFXZaYGUXFcZsZ84SxpgZR5GjwPqF0MctEs85xw=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-11-16 15:45:10",
                    "stockManagement": 0,
                    "externalData": {
                        "accountType": "both",
                        "formSubmissionDate": "2016-11-16 15:45:11",
                        "domesticServices": "",
                        "internationalServices": ""
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "3086": {
                    "id": 3086,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "ekm",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "EKM",
                    "credentials": "gwdBr3TbEXYietikBI+mOpc/BS8iOF8h5kCjQkIjHJQteqPeVH7Kpb2PHH8gA4bUD+nu2YnmfNKW8BNqRfvrwxX0jGIXBEBpk/BXbyvuu0KuYyoguq6K3iTbaM7awC2acUBeK5SpaRSnGYB3zODVtFY/6neMK9b5fQOhyWm2itMphSkEicN/9g6z8/Q3myo/eT7Wj1yf2SaeyA1zrp+MwzrbiVt5/800uYARkIvqqu1dYQdKpKcuHH3a5GA6MLupbPB/CPHldaWGnv2kIdNWiWz/6SVSJYI7jmru2Qnvt/mdHmFHjXXOvNl0b/bZoQaEYm9xwCPC6+14hS4bsFnFqBqoaDnl8/1PmPXoOofQ9WQI6Tuhncu0xVJIONdIN6zhIpCtKK0KylBQ5OBnusHFDUhy3F5WFX+n3K6+WbVWbNWOCqmDOdePsCWM9pTBFvpPJkmHeDeuKfu21by9Gpc1KnnwKdUcmWX+X+8kO2m0mgs9xOrlJ7+WC61TQe93w5/QIIPRmC+CjVhOawZwg16M9U89k26aQMoEWAr5PA/MLIlElw/mVdlNwVYig18fh9hPBlJMcpHh5YTFUosyj1pP7fcDImmodxyoH5GbF2elwB11sfNyALsNFvz7mDBJt9Bec3piOaS3mCGGkwbbuUMSWhbpX6Gd/7hC/ZCp0lvon2AXt/pNAPKZYsPURnShF/D/SbVMF9qDFO0Fd6o8dwsUAPFPIBvDl8MfBrqs5VKa07BqorFZ49QiOuR5jTKDAWpY",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": "2019-05-31 22:02:04",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2017-06-26 15:12:05",
                    "stockManagement": 0,
                    "externalData": {
                        "ekmUsername": "channelgrabber"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1560910068,
                        "processed": 392,
                        "total": 392,
                        "lastCompletedDate": "2019-06-19 02:13:15"
                    }
                },
                "3169": {
                    "id": 3169,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "ebay",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "chosen12010",
                    "credentials": "UA2QeZxpw9ep8cbz5gSaMPOI2S682+fB8XnO7q4128yBxcp2d6tlGt2M63SuwTLOnC+IZv8K4nWetiE4o5APCqm997qiPh1GnrNxcwA5b5uhhDOz2jFcSuJ/F6Yl4QjDQLgPTGwTuoVnXob88cO2d1SLEpHkGjXEtR2XOMi9RNW85FyfQpUtzdO9GmSaExjNiGhPGYeI584qaMJxCbAQ+CPLq8i+9Hyg1L7lwdPONSVsDGruBrDX3JeAXdSEV+MD34HWzyAWqqU2NePseh/zr9ZVLBzXj4tKq7su0yrpbq6knM8mCzqZKom3zxeC6rw7R5RCHDrmP1HkzvS0PpGjWz3kx6/9GJEVIMMWJ3JqglQDiWoBalXT41lbtxFOuI3140msja1qdmavPesf5ZAwHq2ryCCtl7DlRLYb5m2EJyH/mPYS0XP+f2n+DgDNXtQOtclPyR/olO68VClw+AoQHSDnqyh3Zc4WjmaLhpsDUz/5PBwp+dm1NQhTlzZiEXk2RDCScILu2ZPddMOclnrn2a4QXwQGag8AMlq7p4sBMU9MLvO073YdrzcVvxNaXnxAoaIZ1WD+W6OdA4SNjdmlNcB2gR4pNjm88paG6kEd+SGGgTTVddA/fee5kS0OtI1S/ydgGqjLsPYIJE+kTcEFc1uAOkyRVMNTN3T2zwjeleRsLTZ4PkXABDokhmO/r0mo6dxjThId+xA7Sq6Jg7MP+Tu6WveS/UbjQrtC8NicbTgxPzu5Xa9rCcK+/HyC4zwdFaCvC3MQSaGkn9MvsRcVKQklQlOISZaILV10B3/4YM5JsDmDIZr8V2hYefC7JuvVXctaiGDRbsw7Ju58vTi65dNXA0myisoNDR0cai/EvNwHszYzaocCdX5af7NXaeCxX+yitu/J5EPMmDDEiFAND9Tsf1wf/bF83bpalEpKsAaSnbvn5RE+6M8xC+oiW+At8zBLEK8SZb4fzfI5sj96Eu1qmpHTYnAUxvCzcHZmnZAlfmji6t0EfxSe0NFulzHKPPcuoUzgFFofrBhDaDXBteqMFquufFm7+vAp4XsVKgA0yAVdfi2U6niUhhw3G4D5zDbqyoKAkNDS1gnVK42sAAInZQGimQo3xO+I3nNECsbg6eFSWXwHkfgAnAq+neVZjcYm6o+qWv8To/MSw6hWE8yJc94JDu4nGKUFLlv0xE4D31sozgIYDhLggqwTAYyVIzNguxEDBo2t3cCcI1UF/+dgKXrZ3wiV4YWZJJxz9MKcLFm80P/2RITcQN9W3eVDN9wX0XPJsMYNyJp3BTbCdifrOk4hoF87oI+IeM6369uLIq8LN0x7ZHM2+GrZfXh8hSBiQnV9H19JI32/45QXaR0TU1BGVWXENy++mudI8F1ear3PDtUvxIg1kM+qHNaGah6braiv6XkYRLlH6b9YYk0BPQjoCwxyQ4HIzs5XLhr4lRoXA1rxuNeMRwV5tT6gBhsELw4vdVzKDDwhBSPb0ei/cbqc3oj9iYppLI8pwGoCQn/vBqDJaRJHoJcL9ZwE4CXFqgegU3arIGXqSFvxqlXQX6Uu+da371pu5MftsABT9j1yJSrXvfGxQrISF5SIz8gzgSHa0o5rZweOaqeq613Gwvsi8lpdMwCUX/kTGWArxeVZjySm/g28fvUgztpZGkAKcCVnSH2bzvLcvaqP6X3ezUAdfG6y+Lv/dHz3ZUFBTRGU+UN3JYDcL88vCkKrrOrsiXSVlgpo8+chOtnKjxnJFYnIWvLj4+qCBcD1NPw=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": "2018-12-27 09:19:37",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2017-07-05 09:19:37",
                    "stockManagement": 0,
                    "externalData": {
                        "importEbayEmails": 1,
                        "globalShippingProgram": 0,
                        "listingLocation": null,
                        "listingCurrency": null,
                        "paypalEmail": null,
                        "listingDuration": null,
                        "listingDispatchTime": null,
                        "listingPaymentMethods": [],
                        "oAuthExpiryDate": null
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    },
                    "listingsAuthActive": false,
                    "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=MzE2OQ==",
                    "siteId": 3
                },
                "3170": {
                    "id": 3170,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "ebay",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "eBay",
                    "credentials": "YnFTJPtcN2vdvLi3beaLxVmry6ZRq1g7FaltD6+W0fYrDGon1EY6sPOLqIjyUz7LfDd4GSfFV5X3svlWdNQyFNqWO/0nvF+Hte/I+wfv0C/mKOYW3yQ2cfdiTqAcsTkAFZC7gmnS0f42KDKntDyqNqLYvfoMH60r1f5z7GFTlIeeJw0ewRw+Uw5TmveiAcb9Q6NPSycQNxnK7zeAOUvZZ4bsIVWdEwS6wX7K7oP/zjMlSdPau7+E6BjrqkuyfNIDS8Tn9xKvwmigNn2yu1tp8WbrvXXonuAlBYZcmVX1nXwdvw9sMyRQbV1Zm8HB/tR9DloBjadUybCl/dlWFWovHd6xA0d3vgXLDOVF5LpBPLFlOmaAKp24f4Aw35vR8qMW03A6+s8jJvdSBkepwrvlNTiK3RfAx7np3Z9aSBA6P2BVpUrXuUvVwFI30Ub7jLBmIjIIyTsHOIpiM+XPBrQv3g6sdm2+oPh/5k1F+M/6ZAM5Pyml+lgnqEiGdF54EXT1cZiosVxcThC8Z5cg2XmbdU2ZHqQwhArbzZ59ne1moullk19yGywWK3JVeGhy87CHqyyJGZeo1MB/DAikuW0t1Aozage5nhGfkiBzcsrRT29PVyFMGKMWCqNLJQ56dXkllbwd6HuKsxZwGTwnaqJJRWE8bRpaytOjAs9TyUA8Ojo7/+Y4T7ozK3kbP7RMrPeLFFM4rBCCJrsjaDHM3IQOlAQr9AbpOwrb7faBtC22xdXLW3l+WDo+EpulNQ2gNdiyMO9pBinfARuW9UblAJRosEUsw/tgFM9rz55YwVFQpPaMFfJe2EVVWiXsbNlIowvvDARBu2CDm9Ti9my18LHYkLq66NnKiqSwiK1r9fT8jw3nb+UtdTvLXgRIeYCkGkxLcUUGiIdtcbPLdC6U89kmNjcnoTyl9gJQ1q1WzzVGI8FWIy/YLJBGTTRy6728mFnlWrPE6JyCDAidb4V4RE5BQNLFJIY/bICprRoLNUHrIbhjiujhcU/P12NpxtY00r+FdAJmxMO1LnPl2QnNsG7pfEu093Mof25j/NkT973TwdrO/yOsd8sR9KxhzmzKHGl5l3Z2QDyMO2Kc4/mxwUfm6J1Ns/Z3K9eWjLDntck3302oC1Hcm7sVTx8xJ35sTX8VzvBBdspWsavyDs1fCfvwSKhHK4R1zpTTh4a2ZEJV+M6BroxsELe93/3mwTKHhVKK3U+xsLx7LevcRdDpIo6rcP2wylQeyRXsw+d+tw6bb7RTHL3D7Mt5l3dWDpV3KyYGzEQZao+2lm224GvtZd15Ey1FCOBVi/ks0+VYK5bOaz/bPLNDVqCW1deOujg6V0kyHm6Iy0LROngS38G2ZooN8gEaXbUdE2muq7ORC4yXgs4diVPuQo63OubPHMLctFNU/LdgSjXGFyGbzc/TBMy8nxMsijPtVbBCt3A9oUFeIubRLlP3OkXmjPrnNiLQT3NUABFOrDiIH+6DA3fNu3+8o1JDqWcOKvQhRwsRXskuJ5WSpEW39vg8M8dO/F3V6uYe2ET9t2bQhis1CuZmEFo4EMghZZE4+6pco3v+wCTi4plbk/Hf0f9MNUHA8MiGMkCW+ZsNZX5mV24UGbGHcPG+D5LyNPIeUYJTto+yomDArATe+xY1m/PNPgftBqEngESjbv06xXwL4NI+74nch7KDFuWyLLGmZxsisd6u52jybidgkjJzUFrq4fPRvDt8P+XLBjloFlBovlaOjeQGEqJ+2nrBvGBFyvYGrUxiOmXab/6B1o514kFuqz3hQeu4UZjy8Wv/Or9vv2KobqNG/SQ+9Q==",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": "2019-06-10 13:30:29",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2017-07-05 09:22:16",
                    "stockManagement": 0,
                    "externalData": {
                        "importEbayEmails": 1,
                        "globalShippingProgram": 0,
                        "listingLocation": "Manchester",
                        "listingCurrency": null,
                        "paypalEmail": "accounts@channelgrabber.com",
                        "listingDuration": "GTC",
                        "listingDispatchTime": 1,
                        "listingPaymentMethods": [
                            "PayPal"
                        ],
                        "oAuthExpiryDate": "2020-08-16 01:02:32"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1560132005,
                        "processed": 0,
                        "total": 0,
                        "lastCompletedDate": "2019-06-05 02:00:15"
                    },
                    "listingsAuthActive": false,
                    "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=MzE3MA==",
                    "siteId": 3
                },
                "3243": {
                    "id": 3243,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "amazon",
                    "organisationUnitId": 10949,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Amazon EU",
                    "credentials": "7KcoJhcMl9cT5rTLVAL58CRe2hSr87TkfK9zdFb2urppxk7EPnDoisnizeXmEKMFeYlaBwVoYJG/J8rIB+MwSKJkisYeQ3Az/egy0uwOs3UE7Z/UunXuA5xMRIZLI0GkzHYH0DW/67U6/zrSNdNuJ+QXgQLiKIVp93xExHup3yGD0YLHvxNSw58/RoWrRLpL+TXpT6X9VHfigokGHrIu4uwRnyByUyQHaAyeUGBxItx/pbeldHE1T3LJoY8JiGkoQwIUYCiE4jqoLHCwXqzZ9pWQG5LFNiSaGkA7WmOmTsWZbymL7Mg/YA7DzyjuFXS1mfdDFCRN512M1fshjF+X4psCnutqb+k8UgBvEHGedBJtQrfecrFRmkovlWD0Po62JCzpJAGCKIQ6IZqioKZQLEhD4J5AMcmx+CiVHku52/5WvByALEWvk20Mu4nthBHMnrc8whDd1HNzL+Idj6r6PaebVX22E1mb8w7pUUcu20lA9LNwcBoT4VDHHXtO5nPwD4vsPAUykf5es2vuEHZfhXDN4kdJ2s0ZS85dqxK53Qc8IsI19Bifb0Z4ofOFhFApFJ+8Bn6/Dcm8KahJzH76F2cj2bSevXflDLtlbSzQjILyYP9UifJncZLriu6mHKv+b4oshBCzIl03MdWXgoLUrHhgOiGB1jctzTp92TYX0iQwomQDHudS56ZaGGDBMlJtED3F6ojBhNBZQy+vIeBTcbIBgs60H7PgAjbPACtt/nN4zlw/eOFQfUEyWW3g9lqGPaIGwZJFUZeFC1F+DmfyReyoOqOaHhe9HRYWxFVx/QDjTZReHTHtSFIsAVX7HH8lyH8XnJqVKqdIztJWmikcoyj4Djj/LtcOFlktZFRHtZZFZW9i8/gKWj5LuA0QhlwgteoF0cY8KNMqYhpdghRggN3mU66tiXl0hSHLbLsPSORRdJgi4esMT7jXzvFVXhllCqycx0EkrIbnz2HTndq9b6lAdDTxxLIEPcdObHnOAPLPWxlmrxzxxC6922DqkqlK9bmkKtrTHDuN1DrGP2p+Lf3mdrTGoskYwVJ2x89mR0QnnQtQM3jjXst/ujwZfRKtEOMNx1Cwq3uqOSsCTt434A==",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "sales",
                        "shipping"
                    ],
                    "cgCreationDate": "2017-07-11 10:47:32",
                    "stockManagement": 0,
                    "externalData": {
                        "fbaOrderImport": 0,
                        "hash": "3e474f1d119d973829736ed9f7e18a699ff607bf",
                        "originalEmailAddress": "",
                        "fulfillmentLatency": 2,
                        "mcfEnabled": 1,
                        "messagingSetUp": 0,
                        "includeFbaStock": 0,
                        "stockFromFbaLocationId": 2796,
                        "regionCode": null,
                        "marketplaceIds": "A13V1IB3VIYZZH,A1F83G8C2ARO7P,A1PA6795UKMFR9,A1RKKUPIHCS9HS,A1ZFFQZ3HTUKT9,A38D8NSA03LJTC,A62U237T8HV6N,AFQLKURYRPEL8,APJ6JRA9NG5V4,AZMDEXL2RVFNN"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1563850808,
                        "processed": 49,
                        "total": 45,
                        "lastCompletedDate": "2019-07-23 03:36:47"
                    }
                },
                "3250": {
                    "id": 3250,
                    "externalId": "2015291000",
                    "application": "OrderHub",
                    "channel": "royal-mail-nd",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Royal Mail - NetDespatch",
                    "credentials": "TPAJ661Zv8weflgLAfGnxOUuidrIqYz7h2VF+DXL62F97ORyRp43wkY66Xf6AidA8MPWZNmo3QiBHLP5T7/mOZ7v69O1wf07NGm/1G9TvAH8RmJXxSmk069W9MTANKOgEkaKQYpSEyRG23qfYHx5bHgg9gM9+ljgEbbfpwVJSIMo0Ve18dFSGz28O5y74k7tcmbHyFe5NWjR2imIEkvQ75Ph4Dw6Xj2yY3d5W6sc4EjiRAJ7PH/01xkQFGuVkbFIARVjk8qeMnY9qOjuqrWoRUrJEpldvcuTj6VSwjtsImEDc7S8vcBuqtuLHQACUqi0em4OSOsEUa6Uty7rodNGhArJLkhmHX5KMX+tjc+tqunrHgTk1PW4OP0gqJx3PqFKnS/DWlQ1DzNe4/OhBzwQF2+zi1ovdVJCtj5Bt+L1fpYGad82rwHa2j6mTTnQXPNGaa3uBpXKDALBz2/s/XZNIXElriH1/h+UCMupDfDiCiDJ2SeHU8J9HnraiYswLVmlUqL468lqoL/9ALeqXeLzIW0LM5lFz6df8cpZtTCV0Ew=",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2017-07-11 14:38:55",
                    "stockManagement": 0,
                    "externalData": {
                        "accountType": "both",
                        "formSubmissionDate": "2017-07-11 14:38:57",
                        "domesticServices": "",
                        "internationalServices": ""
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "3252": {
                    "id": 3252,
                    "externalId": "3252",
                    "application": "OrderHub",
                    "channel": "myhermes-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "MyHermes",
                    "credentials": "7f0kv+R/TAgt0FTTQa/jAgjjqKeSJJEqscqyXH7V3jN1DE4phocq1MLfFFYGJ0a7AqSagcRmtARNBXvSRlw3nzboLdHgfTKdaOybiEmDmID2zI1cmpNdhi3h1wPelhCWOAkoPGPSCXyndPc0AzVDWHzRte2v76B5WJM7+QuVKgxxELxEMjub5BlN/WbQhjho/rCSTfQPW5Dahflawhb8eRPGKgFq0IdymRAikIXylt0ofznpXkIgxdiqvg9duxViHxmPQ41643IDrsosKt41Bm66fYg4e2WlU00l9ryf7upXbOlhKTFpvHDEDBy/GpDIMp2uHWf10fz1QzOjxdK5YIGwJF2mO9br6rFoN/Y4AVc=",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2017-07-11 14:43:26",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "MyHermes",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "3336": {
                    "id": 3336,
                    "externalId": "3336",
                    "application": "OrderHub",
                    "channel": "dpd-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "DPD",
                    "credentials": "gQR0bS/8kMl1ZdPqxuByLRgIzhL8ifWeqzS9R90xxZP/0lQR5ZhTPJHxLRD7FfS8PLepaPkA34Oix82txMxXWBzj2oWxO58d6+NV8s7QjYwo2Nt4TcljqCBUa2q9ci2bXAAEaFr1Rmnn/Q/DfLyS0NWbPbDjknBP0+//MK0BHZaHU4sQlMTe621Bor8up9S4jQZpUJpc7uksyCJwxG8LlhzNLlOIB7bov+KWx4zfUtKs93uGnGlfoUXylNVFCCwqJBJkyB++HMZIY9HPslnQ4doB8U8zwTV0zcu3hUdCWahbeEPSR8/zIOQn9GyOftzEWqa/3qB6VwLkg1DbtVU8DyCIbhcrzLaz9sOkl9XnMid+ZT5Gp3+w0auL7svxqiKSjmDvG81uifFbJxZL23Xk8EBpl9Sfy2/kTwpVlOlB4sy0Mm2zN1HeiJXb54tPBt7plNPYFWtxF83Ij53uL+cDpPzot8KqlK4DPQ92Yr6xRqoAqY1Vsw5okylz2k48Rw8q7sipDQYuza+A5v8LMjGVQzpl/gl8rh/WtcNxvyL+D4vSWafxx7GfIglUfschK9EZgxp1pC26UWBt7B41zQQIaSqKRmiCKkN8ZxrbQ5TyHUEY8LlZwJBwGxEye2qW6mL2",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2017-07-21 14:20:27",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "DPD",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "3337": {
                    "id": 3337,
                    "externalId": "3337",
                    "application": "OrderHub",
                    "channel": "interlink-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "DPD Local",
                    "credentials": "VsgnLNrzpX0lbxDf762CWR/zHY4J57fFZIPPMuHBWSs7q1woeJfGIhJNV3Q8aJufgNlA6g9ipRofQjMMT1GcGEcus5TQYzLrMag3PekIzx/LeW9HxYeJlS6+mVJ6pNki09+ePPLJfUhkZ0DgTghmh0Vk2/0Qst1ur9IvRmdjNoqDtCF9avYJlq3q2nxxf8erS1x6QwMe6h5BM4vOwJc44bt92/ioK6q+KaUJYrbEKDTv+X9skJuACyDVkrdC2o3KquD3ie2hKWfQ1BeXhlfId1WmO0KXFKZpn0bVAIsYPadjlubUmIxBw0IZ2vLMkkVQeJnmIIdmQ1ZDpD7YvZWNN/wYzHavIBa6+0UEeCfr1AlSw0bhg/F/ZWn4j77njvalqMbDQjFaiTALO2NofS3htvF4RQZVwEYXEKwtYZyaiRKotIY1a2tYAWOQmVJOluTPZfwD3WTlvB26xfE6ENiK7sgLiE6/dbrN4xTjAsTAB6d2Z0Hxm1Oa0xaUIy0/Pyg9oUUCkwzAlyP/dpXbtMUQiLbJxJg9uVMLw44lBf8WXVz5KcJK1/Hw8ZDfPWMz+dIj0uInuaA0kVyxz/xx8IUKSf21a03DibXKRGKDM0WSlhAO0+1gSqPQPLkw+srgAg0sRmzIXBk5rpq8opXd1QLv4ugYvkGs7rh+OU7mcl0rNIHIT+m8Hpn3SDeW66PYhnxqtmX2PAJ7K+jDQaKxmCguEA==",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2017-07-21 14:42:40",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "DPD Local",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "3747": {
                    "id": 3747,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "royal-mail",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Royal Mail (PPI)",
                    "credentials": "Royal Mail",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2017-08-24 15:38:05",
                    "stockManagement": 0,
                    "externalData": {
                        "PPINumber": ""
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "11660": {
                    "id": 11660,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "shopify",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Shopify",
                    "credentials": "2nfg+u+z7qgiHaUqL7yEp5wBIVm2mDW9bW8IRa/tbJ/NVwBsRfGfZD3QNc4CHhkjidsA6bUMGlGTIcVhdvsB+yEecd65eRhg82xhJ6Phmwg51zsVENmCtRvuQ2tjJGpibW3M8gGAW4IJ+5eAdJbvG9jT9+OqlLLGVK4FSZ9+iQoHjKsQ6DqoQd892BOl7dFkcKLmSbKEAXQQXkRU0D9sMbecSmACoa0CSfBCGTEqgOE=",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": "2019-06-26 12:25:37",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2018-01-04 16:40:43",
                    "stockManagement": 0,
                    "externalData": {
                        "shopHost": "dev-shopify-orderhub-io.myshopify.com"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1529671637,
                        "processed": 1,
                        "total": 1,
                        "lastCompletedDate": "2018-06-23 12:47:32"
                    }
                },
                "12354": {
                    "id": 12354,
                    "externalId": "47fwg8cpdt",
                    "application": "OrderHub",
                    "channel": "big-commerce",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "BigCommerce",
                    "credentials": "nYg4OGzKeMmDeVdlbKJRBg3WLTlxIWOK0or+WcNloytYBe91o1iu4DDfhTuCiat72+b+3G8yCA32SCXdB2+mzBxnW2mkSfUq73gHOKhQpNkBiA5dT+XYfHokcdHJErxWps+QtofTX0zFmINbvZOvmAvPpLdUblNMW6DI7IJ2wHoGFlo0UjB4b1PIZJ3HbgDZRD3RQWCIHbXgxpuOAKAJx6dHVtnx6hxqVGMrXB2CApU=",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": "2018-07-30 15:12:37",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2018-02-19 11:20:51",
                    "stockManagement": 0,
                    "externalData": {
                        "secureUrl": "https://store-47fwg8cpdt.mybigcommerce.com",
                        "weightUnits": "kg",
                        "dimensionUnits": "Centimeters"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1521127671,
                        "processed": 13,
                        "total": 13,
                        "lastCompletedDate": "2018-03-15 15:28:15"
                    }
                },
                "12355": {
                    "id": 12355,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "ekm",
                    "organisationUnitId": 10949,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Acme",
                    "credentials": "Xu6vYvSef2w9DAA3fiZvhLYknLkLPyN9lGehw++yU23TAPfkgFESdOvWztrilx4/twu7etv1q/3xrxpQ2ZqZ6lE9MqsxovvQCRYZRTD2anY08cuzlDNJ5Xm7cds/SPcuA0bLkbkeO2SFgVqrc7cF4fJYfO/FLQOY878LYaTvFJL9xT8jx93gzf8TGDctB1IABpkLG3kaZ/7t1gD5adBukAbhzu9CA46r0YyqU4rDqFTGDS2BVp1z/p31ZFTElA42nRsHJdoJ+Q/ICfjLfD+NlELsRWne1dp4Y0x7FzZ6djfcS/ZvtWoPexv7Xz0VcGdz4Bz9odLqI50TOFJ+GPVOeE/XX9k8Hk9Yx2P/j1R082HZpK/NZlKdCD8ovh/g0oO4dSaZNYpKoZs3uUWogN56GvpQlxUf1CtorODaTTwBPPbSpSAEzzbtMfzJJSdbot6lM/hOyurtuwEAmao+V4jZwX7Pqq5DqrTXVZcHTtSUZQ2BXOP6W0ug07rcnXAXAd9dNcmld5d6ywHFhO1U3osZYrbat5niVRORBdmAu3842KhfuHefTnZ9D34H2YpqcR3wVsEd44oPgSY3EJU9n/lvUj/Aohn0Oz+uoOXlXPCiNBl1wQQ61CZSRsQqCF4tWgDdQsUb0wEJJaUSJ/JcnFBiZyEeTk5TnghbxhXSpSvuDcBNgh2cRUhal0mf/8+zRY8rEymTDzRxXoUd9InS3YJS64dfwJvJ4OOmq0bI1BXlMXs3IX/XAzS/Cyj8oAcw3eIx",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": "2019-05-31 22:01:11",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2018-02-19 11:37:54",
                    "stockManagement": 0,
                    "externalData": {
                        "ekmUsername": "channelgrabber"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1560910676,
                        "processed": 392,
                        "total": 392,
                        "lastCompletedDate": "2019-06-19 04:34:37"
                    }
                },
                "12628": {
                    "id": 12628,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "woo-commerce",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "WooCommerce",
                    "credentials": "n74zEWIEcNi2aN1IiseeY4EBTnZxbT+pIt/V6XmdNHJSxK/0SPaiiSWKFbV0qSJtk88YwMaTf2wIgN+32hsCPIertpodyhhccXY1D5C72z07zEe5R48fxaRtPOcZDdbcwQzKbdz6qMta9o5ZyAlsVqYmezGrQ+tugX0sx4wACg1caskGqzGSjsrXMsSzTeG5/S7B1kT9qhXXE7vGBks03Q3l1RK2lbdd1ilO1WorAQZVtZuCugBuPuINcADQ7RhkqloG7UALR5QuF3oTdofh5ZrqKwx8c0FCQErZKn5El9iWO7NvgaHybiizYrIPDUoAacRxpJXx8Z4BjlSrItmwlIMC1XPr/jzOh9CVU/9i0Vo9BkoOpHGXP0ykzP2fHdw1hRaV3UbnEe7QnR5Oqf1t5wdfCNOuVEG3cNTJqSY87l+6XBtN0918lq1vT8p2A5n56GuKMRsuzrb5afgNAfDmXA==",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2018-03-09 14:20:55",
                    "stockManagement": 0,
                    "externalData": {
                        "decimalSeparator": ".",
                        "thousandSeparator": ",",
                        "dimensionUnit": "cm",
                        "weightUnit": "kg",
                        "taxIncluded": 0,
                        "currency": "GBP",
                        "sslEnabled": 0,
                        "sslUsed": 0
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1555459231,
                        "processed": 92,
                        "total": 92,
                        "lastCompletedDate": "2019-04-17 00:02:27"
                    }
                },
                "12917": {
                    "id": 12917,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "royal-mail-click-drop",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Royal Mail Click & Drop",
                    "credentials": "FyGQTsTo6FxxEgfZP5u/mg3S1GYllTb1Fy7Rs6Va50hJKMOPelFoKHpygFXmXHysCbIR9GpAjIdCpopxwHuwvFAe5o1azYz+WlSKG8VGPJuDDFPhZ2dFPlW2s8DScHpjFO2TnH2D+7DmauR1W/Ttm2v8FIoWY2Go7+S+GE3fq1wZmf3eESt84Dn8hsx39lzz",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2018-04-03 13:25:10",
                    "stockManagement": 0,
                    "externalData": [],
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "14098": {
                    "id": 14098,
                    "externalId": "14098",
                    "application": "OrderHub",
                    "channel": "dpd-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "DPD",
                    "credentials": "uUXAEpiPi895LUMm4TvfhhQDFNZUt6mMfjkRyqEKtBODeuPnx2f5EmQUhp8AQmYbMbyM6B7amdpHW0alXAG7gt/ufw1Ndhb+iK/q8if8V6pNK49HGba7f1jY5/0d9+QEN0TAeadR0eSZeawW1BweyvpGx1b6sLF21ejgecRKHPtFPniv/Ym6EO26D9OSyyZSIZygyintBBX7r9fnCiCA2BRk/IR49CrdYTz5jeEubd8ARsY7MJXZjE6O6/TePqYKzXio4Q+GnA2i4Tc+dt9WbfaiRHQjpT5daot6wDEWeIDtm2fe4w+f44uuitY9S5zSVdcMFO1Piu7LPk6ohoebtoK4OQUZpQVJDMNlO8vgX4zbgT1GxYzDx1FdVtniKnp6eZbt3rp+2h3WNbN27w3NoMWQa9Lo9SHaz6zHgnhTveTFQ/oW142T1n3wEzE4qKAurT2hOix61b2uUO0wP9R95b2v0ryLxLRjmI2KX2pp08UBa9VAgAh22fI1KhK38LVigCE+0doPUcEnF0FeogihMdtC9ZFRP13az2+sfw/p+STwbCpujEdfg4qpDLRAkE0saEUVBBpzSA4ipunEm++PrsqDv2jXlMGeP+ViUD+m/BcW5von+M/d3q9JsjT7hzak",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2018-08-23 13:00:52",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "DPD",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "15504": {
                    "id": 15504,
                    "externalId": "15504",
                    "application": "OrderHub",
                    "channel": "royal-mail-intersoft-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Royal Mail OBA",
                    "credentials": "cbwMXR2z3oeu91Jcqi8PbLZwsbe6mRSmKotJNhSP740WFyH/xN+I8AzruE/RgDS0urTeMdRLX/EaaeVaVszk/SnJVyih4VJyOmskqrzzGVrA5iaPGM1nLcY4rwzZZd9DFNLYqEoSbqgJ141orsmCLVL2ApOLY7SpfePZywCmFnKlTJ3FDsSLix4kX0wjuIWi3QJYAMFahOrb6RXR24BVijZ/x9mniiKUBkzJZccDuvGcpRLcNWgnl//d/Hspnum7fmCjhcVZpeKcTjyCPlNKyrbwqtYYCyiADvh4zzQt1rFPR4DlSE6z87M85yPTiAr/1UWTohXOrE/vOPSZmxw5cOg6Y6weDvi2GDtu4CZvR2+Q3k89NyyAIT/VLGMugWmJKVf+aibk6dpd5E4URa23CA==",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2019-04-18 09:29:14",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "Royal Mail OBA (In)",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                }
            },
            "stockModeDefault": "all",
            "stockLevelDefault": null,
            "lowStockThresholdDefault": {
                "toggle": true,
                "value": 5
            },
            "stockModeDesc": null,
            "stockModeOptions": [
                {
                    "value": "null",
                    "title": "Default (List all)",
                    "selected": true
                },
                {
                    "value": "all",
                    "title": "List all"
                },
                {
                    "value": "max",
                    "title": "List up to a maximum of"
                },
                {
                    "value": "fixed",
                    "title": "Fix the level at"
                }
            ],
            "taxRates": {
                "GB": {
                    "GB1": {
                        "name": "Standard",
                        "rate": 20,
                        "selected": true
                    },
                    "GB2": {
                        "name": "Reduced",
                        "rate": 5
                    },
                    "GB3": {
                        "name": "Zero",
                        "rate": 0
                    }
                }
            },
            "variationCount": 0,
            "variationIds": [],
            "stock": {
                "id": 6945863,
                "organisationUnitId": 10558,
                "sku": "EXRED",
                "stockMode": null,
                "stockLevel": null,
                "includePurchaseOrders": false,
                "includePurchaseOrdersUseDefault": true,
                "lowStockThresholdOn": "default",
                "lowStockThresholdValue": null,
                "lowStockThresholdTriggered": true,
                "locations": [
                    {
                        "id": "6945863-464",
                        "locationId": 464,
                        "stockId": 6945863,
                        "onHand": 2,
                        "allocated": 1,
                        "onPurchaseOrder": 0,
                        "eTag": null
                    }
                ]
            },
            "details": {
                "id": 1888934,
                "sku": "EXRED",
                "weight": 0,
                "width": 0,
                "height": 0,
                "length": 0,
                "price": null,
                "description": null,
                "condition": "New",
                "brand": null,
                "mpn": null,
                "ean": null,
                "upc": null,
                "isbn": null,
                "barcodeNotApplicable": false,
                "cost": "0.00"
            },
            "linkStatus": "finishedFetching"
        },
        {
            "id": 11400134,
            "organisationUnitId": 10558,
            "sku": "EXBLU",
            "name": "",
            "deleted": false,
            "parentProductId": 11400129,
            "attributeNames": [],
            "attributeValues": {
                "Colour": "Blue"
            },
            "imageIds": [
                {
                    "id": 13812565,
                    "order": 0
                }
            ],
            "listingImageIds": [
                {
                    "id": 13812565,
                    "listingId": 10222599,
                    "order": 0
                }
            ],
            "taxRateIds": [],
            "cgCreationDate": "2019-05-03 09:28:15",
            "pickingLocations": [],
            "eTag": "5e0eefba90b8832702f523e2273fad9394aec07e",
            "images": [
                {
                    "id": 13812565,
                    "organisationUnitId": 10558,
                    "url": "https://channelgrabber.23.ekm.shop/ekmps/shops/channelgrabber/images/excalibur-stone-not-supplied-103-p.jpeg"
                }
            ],
            "listings": {
                "10222599": {
                    "id": 10222599,
                    "organisationUnitId": 10558,
                    "productIds": [
                        11400129,
                        11400132,
                        11400134,
                        11409247
                    ],
                    "externalId": "103",
                    "channel": "ekm",
                    "status": "active",
                    "name": "Excalibur (stone not supplied)",
                    "description": "Wielded by King Arthur!*<br /><br /><br /><br />* we think",
                    "price": "2.0000",
                    "cost": null,
                    "condition": "New",
                    "accountId": 3086,
                    "marketplace": "",
                    "productSkus": {
                        "11400129": "",
                        "11400132": "EXRED",
                        "11400134": "EXBLU",
                        "11409247": "EXWHI"
                    },
                    "replacedById": null,
                    "skuExternalIdMap": [],
                    "lastModified": null,
                    "url": "https://23.ekm.net/ekmps/shops/channelgrabber/index.asp?function=DISPLAYPRODUCT&productid=103",
                    "message": ""
                }
            },
            "listingsPerAccount": {
                "3086": [
                    10222599
                ]
            },
            "activeSalesAccounts": {
                "3243": {
                    "id": 3243,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "amazon",
                    "organisationUnitId": 10949,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Amazon EU",
                    "credentials": "7KcoJhcMl9cT5rTLVAL58CRe2hSr87TkfK9zdFb2urppxk7EPnDoisnizeXmEKMFeYlaBwVoYJG/J8rIB+MwSKJkisYeQ3Az/egy0uwOs3UE7Z/UunXuA5xMRIZLI0GkzHYH0DW/67U6/zrSNdNuJ+QXgQLiKIVp93xExHup3yGD0YLHvxNSw58/RoWrRLpL+TXpT6X9VHfigokGHrIu4uwRnyByUyQHaAyeUGBxItx/pbeldHE1T3LJoY8JiGkoQwIUYCiE4jqoLHCwXqzZ9pWQG5LFNiSaGkA7WmOmTsWZbymL7Mg/YA7DzyjuFXS1mfdDFCRN512M1fshjF+X4psCnutqb+k8UgBvEHGedBJtQrfecrFRmkovlWD0Po62JCzpJAGCKIQ6IZqioKZQLEhD4J5AMcmx+CiVHku52/5WvByALEWvk20Mu4nthBHMnrc8whDd1HNzL+Idj6r6PaebVX22E1mb8w7pUUcu20lA9LNwcBoT4VDHHXtO5nPwD4vsPAUykf5es2vuEHZfhXDN4kdJ2s0ZS85dqxK53Qc8IsI19Bifb0Z4ofOFhFApFJ+8Bn6/Dcm8KahJzH76F2cj2bSevXflDLtlbSzQjILyYP9UifJncZLriu6mHKv+b4oshBCzIl03MdWXgoLUrHhgOiGB1jctzTp92TYX0iQwomQDHudS56ZaGGDBMlJtED3F6ojBhNBZQy+vIeBTcbIBgs60H7PgAjbPACtt/nN4zlw/eOFQfUEyWW3g9lqGPaIGwZJFUZeFC1F+DmfyReyoOqOaHhe9HRYWxFVx/QDjTZReHTHtSFIsAVX7HH8lyH8XnJqVKqdIztJWmikcoyj4Djj/LtcOFlktZFRHtZZFZW9i8/gKWj5LuA0QhlwgteoF0cY8KNMqYhpdghRggN3mU66tiXl0hSHLbLsPSORRdJgi4esMT7jXzvFVXhllCqycx0EkrIbnz2HTndq9b6lAdDTxxLIEPcdObHnOAPLPWxlmrxzxxC6922DqkqlK9bmkKtrTHDuN1DrGP2p+Lf3mdrTGoskYwVJ2x89mR0QnnQtQM3jjXst/ujwZfRKtEOMNx1Cwq3uqOSsCTt434A==",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "sales",
                        "shipping"
                    ],
                    "cgCreationDate": "2017-07-11 10:47:32",
                    "stockManagement": 0,
                    "externalData": {
                        "fbaOrderImport": 0,
                        "hash": "3e474f1d119d973829736ed9f7e18a699ff607bf",
                        "originalEmailAddress": "",
                        "fulfillmentLatency": 2,
                        "mcfEnabled": 1,
                        "messagingSetUp": 0,
                        "includeFbaStock": 0,
                        "stockFromFbaLocationId": 2796,
                        "regionCode": null,
                        "marketplaceIds": "A13V1IB3VIYZZH,A1F83G8C2ARO7P,A1PA6795UKMFR9,A1RKKUPIHCS9HS,A1ZFFQZ3HTUKT9,A38D8NSA03LJTC,A62U237T8HV6N,AFQLKURYRPEL8,APJ6JRA9NG5V4,AZMDEXL2RVFNN"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1563850808,
                        "processed": 49,
                        "total": 45,
                        "lastCompletedDate": "2019-07-23 03:36:47"
                    }
                },
                "12354": {
                    "id": 12354,
                    "externalId": "47fwg8cpdt",
                    "application": "OrderHub",
                    "channel": "big-commerce",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "BigCommerce",
                    "credentials": "nYg4OGzKeMmDeVdlbKJRBg3WLTlxIWOK0or+WcNloytYBe91o1iu4DDfhTuCiat72+b+3G8yCA32SCXdB2+mzBxnW2mkSfUq73gHOKhQpNkBiA5dT+XYfHokcdHJErxWps+QtofTX0zFmINbvZOvmAvPpLdUblNMW6DI7IJ2wHoGFlo0UjB4b1PIZJ3HbgDZRD3RQWCIHbXgxpuOAKAJx6dHVtnx6hxqVGMrXB2CApU=",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": "2018-07-30 15:12:37",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2018-02-19 11:20:51",
                    "stockManagement": 0,
                    "externalData": {
                        "secureUrl": "https://store-47fwg8cpdt.mybigcommerce.com",
                        "weightUnits": "kg",
                        "dimensionUnits": "Centimeters"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1521127671,
                        "processed": 13,
                        "total": 13,
                        "lastCompletedDate": "2018-03-15 15:28:15"
                    }
                }
            },
            "accounts": {
                "844": {
                    "id": 844,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "ebay",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Calico Trading",
                    "credentials": "iCpnvOwePsMJq7J40bqlO77erZ5X+00dzKvuRk4PpSGCEsEYCixvrALXTh8lZ4anCsePIJMLRFc8MN0C2DNL7WWBffE20qfU4ZmfY6BtJjLVpXn3Y8/aLas6uI7BYX+xydtAavkSmiGJOLPEtQZqCpHT008zPFTA59ebB4tDe2DvZHIZAPoxMX+QfpaaujyBxpzw3RGmId4C6LzUJ2G5meV8tzw92/SMU5alnWCrX+p1LUK3tk7CJRFDU6PSOn8Lh8ZegQEAoMUGMEOCZuIvhopDmYiCm2PLvk1f+IofZXTufQtAjZBS5yyDTVqqKSS056zp02tyh3J0aATDFpVONkJ3IaTFRNpH0eG3nwwsI0RgaRPTNVr/c2Nhf/KblTE0P8iOus8UJZTIesgXQApt2yvUr/P/X/VD0gkXZO/nREmdRqAerC1Usx5mCLvAUBYoNo3el8jsdYFX2ykzbwFd0cHJGaQPujEdjmR4ELs/llTelUGT6v+MIrfw9cZQ8SrP2OziAP5lsrr9tqi9xG45dGas+/jCOWdU8eAxm5rcQEtDlWG1Kk74tbwWFLqMgrKIVE+yx5Xtud+cKgEp2IDD+4bc/7plEJBW0XQ6nMJPelfKq4DnQe4vw0hcgyJjAzJFyDQtN0xVlOmciVHRi44PTgEFKTVUmwBzwvxsNeUR1an5qeZ67gOxRHgndI0QVq3aKI8vm8+1arW1Hg7iYYbdoZ0L+Inl+SGRdQFVwfvgmLjV9YacJV4o/m2X/RUawj7i386r1HSitafwnICDgsOk/psvSb7phj4Z/2jxx+E5VjlW38v6bVpk6UYuGimbVyh9gqNGq3oX1rRPG7jAiUQTGIoSFt56BJFAEyDMXqNnzv3c/nYm+gTw40pmhPPAUMb30ZMecHdIG5ulqgaQaxADOM3Lc4VddBhFO9ejdIrACf+Az+TI4dzGgDnh/62yrS6hkdP5qR3N1LUQmyEgDH386oy7uQsoe57Dnuy29YNI9ijjC/3Zlf0k/O0SzqFCKGDOTOWPDA8yj5bw4ZnwyWE0Sl7FF3QshrhdmMlJ8hZz6oE8M3J8ynNPNzHl4k+ItplWSp+tnOgMv8r6CZ3/jvW1vfUQ1z2kzw7g8dt6NcQjFTbCAriDdhJPYTgeOtDRwaWpHuFrezA0suhYNVo/7CUyGzkOk1XFpMazNlBUKDFHFwGAHLMRLTKivg0r/8pQzoqROxUjDedGs8YXQNRAkQzdQx2cTEwW4yJNrEn9j8nFD+84l5j+xKTQfwkbfQ0AzVBO/psTYA4PAZDArtxqxiTroiMNdaZ3P8vXDpojkardR2QKsQEEoInXaGHpNzxLVdnrZcbRBCZMaWacecUH6H7vE41PAnslbm6E/0h1gCHK2tqYCLH1M/iYTL/hp64nPlPyCb3P0/TGu/gFcamxSRqPF4cP/MnENAtgIW9UxRsEEUbMVSvYxg9MtkADggF9pmL2L4Crkj+FbTZ7+yhRxhU2ycwbhZzoEXDOqPauxnDEXIbXlV0gJrUnhwIcA0NQi5JkyZukM3HjvWX4j/MB1mFsKlA0wdfVYmh8kIFr6bLCfjuipbC/sUIB/93U+rvSGiaVNqM52w6dJjIQZ+p9eDJzKyHy5JIipPRhCcMpBx5xnUA9rlwhOhy9wKzxRfUQApXOPu2MavivSO/8cP5mLdkylbH3T1vBBcuSVcHhQ+Wvhpd4R1zIAt8EtZyfSJgsiw3EsQHXfebAoKffXQNKX63T2bXJi4WAOrRYjPAsey+YmHk=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": "2017-07-20 12:16:35",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2016-01-27 12:16:36",
                    "stockManagement": 0,
                    "externalData": {
                        "importEbayEmails": 0,
                        "globalShippingProgram": 0,
                        "listingLocation": null,
                        "listingCurrency": null,
                        "paypalEmail": null,
                        "listingDuration": null,
                        "listingDispatchTime": null,
                        "listingPaymentMethods": [],
                        "oAuthExpiryDate": null
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": true,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    },
                    "listingsAuthActive": false,
                    "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=ODQ0",
                    "siteId": 3
                },
                "1096": {
                    "id": 1096,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "royal-mail",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Royal Mail PPI",
                    "credentials": "Royal Mail",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-05-25 09:55:08",
                    "stockManagement": 0,
                    "externalData": {
                        "PPINumber": "HQ12345"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1445": {
                    "id": 1445,
                    "externalId": "1445",
                    "application": "OrderHub",
                    "channel": "parcelforce-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Parcelforce",
                    "credentials": "2+p/GVQs1ndg/heKwGT6bePmrr9ElapPzIhmSYdggFDPypxFY+/sIyYl5nWNhBpugPdB/rWFnyon41Trir9I1tPLadwkG3tx4nXqeN1Fs417/NKHRZtZw2pYcLAOYiJO5egBD/wtYAVOWwTie99HiBsOXxjuOifLQ3/eoo2lgorjmnQeRJ5sKY535YOsHS3m0F47C2ypo5emUIw3pXCoSncxdDydOmrY0H5tJLUIA9nGZ7DDuNBQyfFuu97XsIExuriMw3qIg9MXPcAFy56silpxXdE8qMAlIN9NNJQqlcSOt++u6XpoeO6FEHXmvc/186H3Pi/XXwp/xpr7+0Y8FK6K0/rPga17hGWRLY+AidVnNyYl7qc1LljcEmhSXD58fpzMIOcH6XRjiV/giHHZ4EqTKBMIBpxwJ8fpqpJAGAlGs7t05vol/44LQ37cVzNp",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-08-30 14:27:11",
                    "stockManagement": 0,
                    "externalData": {
                        "config": "{\"emailNotification\":\"1\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"1\",\"sms_pre_delivery\":\"0\"}"
                    },
                    "displayChannel": "Parcelforce",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1447": {
                    "id": 1447,
                    "externalId": "1447",
                    "application": "OrderHub",
                    "channel": "dpd-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "DPD",
                    "credentials": "ljkIEIyzleoeSE6GrLzXJXh9nlRkVmiWu+tEm023Qsld2iu0461qm3LK9ZmwxZ110Jh/PBp8E1hUuUd61B/7cei8QWZcF8qjAq6IyZnkL+MygqJrScSdbowuiFSJfsw2oKiNH5pkLZ37HMyi/s4bNkCTOCzNIF+QBeWDX7GEXwXAkBhMGUIrQcXrjvf/aJV6+9D2Wv3TZqXRrZHg8HYqL7KJm1f9FGQ5H6Fxsn5Ams7+qTcTfV4nxKB7mM2aQxLbPF2rz0B5UU4kKQgLjc6p6ISTm+HRkEPqo+TQMZU9diBQOlrEm5MPBDK/y/QKZf8SqtEG7L3VKSw5pbpyThRUvcEeWsq5eW+r3zQ1bhzOewYHHD3psQWUlWlWC2+ERO430xrYDiihs5gOBhtG5rYI15g5Hz7GrRSPXTJl2KHeOrwTUnKVdmgOTYFBNwiXB9yHAMw79394xLhEpgeoZAon59z+n/kgCV+xf3164Up2DNB4ZXeC0bKCwZS5UU1aqGV8imcBrsh45MlaF/jDeRI+ZoWhOUjGdJZrqibPhAKnOG0PW4028tQ7WUwl1Q8qZ10AQRqQMTIChoiTVr/CYJ+P+fW0redHDDXzi2jSa4sp9sPnsmkCIP0wuOkZU3yxawpi",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-08-31 10:08:30",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "DPD",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1448": {
                    "id": 1448,
                    "externalId": "1448",
                    "application": "OrderHub",
                    "channel": "interlink-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Interlink",
                    "credentials": "NhqQH5Yvo2mTPMAitLN8XOZRGJjNKbu4ld0zvfLH195fntGbuiTG++69OzEB8uB0xUkNuWl26t4ou+Xl3A3pG8Nj4doKnuE7Tnztrn82wVrGMkJHElVGs06ffZdFvG0s2MKehldhazxC4ycuEbjDX/AQZkOTULoat+XeDeujvZnN2xxB+o7xpx5FOjeJTyyypjoFa8MEtGQovHpCPYO7ph/Av7MU15q9doddvrARtiIEK987xXMSnei8Q+eauGWUs+74javCusSj0u5QKPLOoph/iUNtsU6XPuMgSbUvaNpQBIR4HVo/ztnXlOx8JeNC+TrnBQX13N+5I267uYhMNpZyh3I4jj2IE44WeJvWCCBCw+68U3UR4DMccBKx5ClJkReekIwl9D9KNO+dK1lEjL62B9peb1JQ+RgFeulo1XB4otF/cJXD9MeliZVDF8P2rR3v7QhyHfpMPQdOu8w2/blnjpu1PUdUPQhUVACqPNJjYpLLSeSWZjZaTENJs/lTTmOSUAMYMsVwCNAWQL8zpgxNvK3PmtStI9g4uNhRPUUgt1d+L+Pu/wSqkVhqQ24YbahGfPHKCC09QODqBBkgaHk0IlcVIsKLOJ5efJBCP79HOpeN5ZvZpBhhni+yAhDxeRlk996cQJGl85xiVHGgU6Tf1KycBa+SWeKj+y90s1aKVU5yLhEJL+DNeq4vXHWMt5KvQoA2si8GMUoKzDnP1w==",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-08-31 14:28:29",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "Interlink",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1456": {
                    "id": 1456,
                    "externalId": "1456",
                    "application": "OrderHub",
                    "channel": "parcelforce-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Parcelforce",
                    "credentials": "X5d7L4B6DUXntVIsKEC6J7ULjviSYN9GsxICofFbFW6PswrPlEmAdeK7IU7ZjFrFRTPaP7W6e/Iz+jG+KqKQNCLVF+B2ggau5v22zwx4KGTl1+9TYhkfhGHXhA95m2l5sVBSNOiSr9ly/kprrBXa7l22ouWiXYXt2Fzcx2VbDjYd4zAIN1Tp5N80alyfkRzVM/RoQJ9IwFVoFHqMXE2FVPUz5VAriZ9LM5DTJHUYuS2bZ8d+s8c4BOzrwi7NZhEzzsbWtDF9gKNRKc/wqKW3idSMPGvSJTnjCGMH9+7FxHXhYN9BE/igqnluhIxUHttJ7A4FQw3yEypyDDybfJzta54pGULumsMmqkBSOZ69YgKCrYpgxfZdhfnzmy8hIiAwoTOZVsgQbBP4rcbFyyD/O+pXGuVh3IDeclenPbv3i0jMu0SsVFDwI5QcDoostNQMbhCe/+nuTvREI1p86aJyAA==",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-01 11:32:39",
                    "stockManagement": 0,
                    "externalData": {
                        "config": "{\"emailNotification\":\"1\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"1\",\"sms_pre_delivery\":\"0\"}"
                    },
                    "displayChannel": "Parcelforce",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1457": {
                    "id": 1457,
                    "externalId": "1457",
                    "application": "OrderHub",
                    "channel": "dpd-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "DPD",
                    "credentials": "fG7mb7o2273LKUcn60vpLqfgxMp0G7LArSWNJBhjacburSEe4dIIXIwv33UPngs02UrfSOtf6YxYLQ6+efXNa6NbF4r1WlcVlbsQq4kBrkPtHWnfJE/IUgj6FNC0p4vqMB/3bwaV6f/gJgkSeTMmTGnRtr36icakeFbgOG+n4mBJhMpH+CMErlhJnO3+7Kq7PoAaA/1EZyHSf5hMBnrU4ZBrFEaGChToDRaiZGPgAiFWs02BlzVXAFLQou3FD+UauH+zbW1kRXCd+OOYTG/ew4yPNPB8SC3CCHwci5QiESVIs+q/qCApLMBVPVq6/EA8bghNsO7VllIRhUqNaHC/X+K9IePaplS38FV7nNd8twLayj0Fv7JSNqD8BwgVWM+p5geadxX9T05fQ5ijqfCP3qablNY1hJWDQMnxbvhExxjSO0BPvaafYOHE/HiokdsCDjLiiBCa4q48O/tiLMgaR0kjpFmD8xcmZj5+fPKTCXKd6jssI9pTEtoon9dQhCo0S/kF174ke7r6vj/9lKr2rTdVGlhNoqhhxNet3AeXppMk7PZ2JxpiYFQIy3CTuCs6Cce4c3Gdn1Ws/iSZi/9PpMhP/hvUxYDO6SMN5AmI7S0=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-01 11:51:43",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "DPD",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1458": {
                    "id": 1458,
                    "externalId": "1458",
                    "application": "OrderHub",
                    "channel": "interlink-ca",
                    "organisationUnitId": 10949,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Interlink",
                    "credentials": "xt5MOjdt8njG8ACdxh7Bj0wTCGxMqU0wJXIoG8bM/JuUSSpCM7P+/P7OhAjROM1jnbeSBpT9UDmfgc23CaR2kW/ebcVqrRMwWsxDoC0yzR/adLgTn+TnV7JqGWYV2Te06IV9otvmWG30mOSvrawVTXM956dun/Al/hUAM2E8CJFFnG9nG11DKXfa7CB9X9PeCGGHq+YRuK/n7xI+s7WblT+BU1YSIyhGiSvzKCYIrNtNwDjq7m8RqDsCtYNGAUAufF2pACZKU5L/YF7ClH+5pzwAFalqepI6GjrnMkO5gIMHA1qpoiYBDlSdctRGIeteCz52n4vvlwHAhWQKX/URUiRm8JYUdwCKcRvKw7SuYm4DN4nEnjA8oVNOA0zvMMtapgvuHQDX10NJ3Zwahh8BLQo2XvjFfi8uHkJRYP4OqbCULWard/8jBosdZOPtXJFXF0ZGXuxQEm/vjNOfz2wOlhXAK8ppZsm3YV3xDv8cIglkWExxs9z20i0IBQYjON6xmJLymqwqBmWFo9AK8KPZ15pmOoWHOgAcfpKduqUbNoTNEfnLkDz+eYnpXvWRj4jy5myjKUoi/QaLBBBK0G+bH+61cAgsW8bwaI4Wl1+Hqc8OlWc7a+mrmPpSNj7291kr3zO8oM+C1SASiXjo0oqTfiGiC9jeMSsLRiTz70gLn83dqaLkwbtvFsI9z1JPVA27",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-01 12:01:39",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "Interlink",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1517": {
                    "id": 1517,
                    "externalId": "1517",
                    "application": "OrderHub",
                    "channel": "myhermes-ca",
                    "organisationUnitId": 10949,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "MyHermes",
                    "credentials": "h1S6HNiK87jLSSQ9mQdgnFR3kwFgymBbpYfKOKTOqA6W2GDiHqs4GRc8HFPlDPA4quy31sQY+v7aVieuGjrewV0mV7rExhiVhpnPE2e6YOr1OVk2FS3VVfVSKKhcMy4RVBppxlE1hPgW+Mwe8WHtW10AFemp62BcTQNXsSIzfMwNJVjYpm3yZklFUMWUiUMqJAsyi7QkZUKVOY/z36k0FVgYPMjeq+WdaUm4T8jvmVXJtLJheQjpiYD8C8vFutCMC5JwCEAOJp0EPUiniz+FhkOI7b3s1U0wUt8R/aI4VD+R4JnHhohsCJHfupcz9xMQbc+3FeociXqZJJ8JLHZSRHE1g9FcBCzHHAMaFT8z3Tc=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-19 14:43:13",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "MyHermes",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1518": {
                    "id": 1518,
                    "externalId": "1518",
                    "application": "OrderHub",
                    "channel": "myhermes-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "MyHermes",
                    "credentials": "KhhFj5g00yulMijSIH+AIhJDZOKhkCOvK8bmDmK5ZiL08EdLU1JvIkoZdb+/nLDwuv78t6Mkh7zjHnj2QyrDzVBYmYp1gIP2otSCu84PvwFEOgRIEGKXIp18kwYHMBkhE0HryaoBVwYnqORH5/vhVz2rmUl3q33+6F9oeKIEGziK5vqf8TjDXJklCGCahkQe+zjY1cPzQc43pLaTI8meQ6i5Fc2NtMglKrStfE3sysmOH8Qw0aNHzDs0R6egbZvvxbvcYDl3bqk6qpllOE6dqUTYu5OSkYXN5ckY2BzyuyjgpF5Qbt0ytCFp5WhngpdcsAzPBSJsbxLi45+KvUcnBCrtlxCbS+0kzxyj470rTR4=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-19 14:48:47",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "MyHermes",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1519": {
                    "id": 1519,
                    "externalId": "1519",
                    "application": "OrderHub",
                    "channel": "myhermes-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "MyHermes",
                    "credentials": "9ss6kvrwHKcLDKIZomeQeosQVceVYY83oaK2gEN9EWvxyZBgc36LXjeFXjzCqNdZgPJova1NC8lsOmxLEQ6rQkqJQ2ORcwP61RON3qtTVmOtbRodGWI8F6Tif5l4JVwN3V6U0eYv0HeJIAZS5P+y+qiEWNteN3oMI5s6O2Z5ROpFJm4Wmtr+mWhJstqHXxzfVowEr0jgzVV9ovv/I3ovHrm2oR18pJpQ8F9hmbKlWS8Mx/tsuprfKDXHB6yY0TtY1A9rVP/yVR6idBExH0WovwBSiWH/w55ZoqTdrNMvlnIIdQ0VBFezjqlg26DQQsvfnbWToT771KD0iskq+2HTY2vwjQndkoFFQXO0aX0Gr9E=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-19 14:49:22",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "MyHermes",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1520": {
                    "id": 1520,
                    "externalId": "1520",
                    "application": "OrderHub",
                    "channel": "myhermes-ca",
                    "organisationUnitId": 10949,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "MyHermes",
                    "credentials": "CktJet0qBgjEGaDOHPUDWB6KCpZAtwnby4eUv3mGBDbA/xa91TRzfWZp1W4R16wOwB/A5EXvYZMpxQZg4XtdRDnfapKH+QpL6zD3ppFnXlwU51dBEq/X3ulpR2VUSPoxzaaKsFW7BsopLwlNMkBg6XKPiy/VBawcdGocWFgZUEuptaBhywgJaX34BV5ozh3aECMrB7P7zHfG2awMDizXerCg2zjeiSr4oTiL1ohbMMMYoA+dr5JIWrCpk+KIUSymEkjgeHS1eOSSr/XqoaZ8RrB45XVYFzIlOXEsGydlGA3VTVhCNE6E6AsLmO0pWopCSx0aQDc7oUk04KDDFaGSg/i3aFMIxxL2s1RzO+ucQ0s=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-19 14:53:24",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "MyHermes",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1822": {
                    "id": 1822,
                    "externalId": "1822",
                    "application": "OrderHub",
                    "channel": "parcelforce-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Parcelforce",
                    "credentials": "S4gu60+S+paiPJMW7SRwvl1pT6QI2trrbjLFXSU0W52/RtzGLT10+TbGnh65r1EL8/imaGzb67YQgbU/zDCE3v763VAP7gKfrq6ifPlHzluRaXQteGpmKzQQWPp39q6XgTzzAANLI2otAzTrQjZXYY9fUCaYdGyR2QmxOdhfZlbJQBq7cmvOHk08fPy+3DYc9sIGnOXLLpdS1rJ2apJWY03oS6d9DLwXRvfKPrwHW8mofDFl+WK4gZyRUcrlLTp2v2HrzDw9TPkqs7chL+COpbscgph4soytOYCrl/Tq2gAqjVjoC4xaUCzrbZ1RY8U/GpxFcwvJW0Gi6ZgU+4UEYLFeGa5He61pExi+bmwp2Wbase4DJjfipO4anqqwySM4iC/xjKJMg7mD8CLWoLHqzsYw4ZvrM7tg4pQo8tBVDlidp7S+DVg6nDMgogppJ4XbOLW+/62n18TvD1DJNSGLDQ==",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-11-16 15:41:22",
                    "stockManagement": 0,
                    "externalData": {
                        "config": "{\"emailNotification\":\"0\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"0\",\"sms_pre_delivery\":\"0\"}"
                    },
                    "displayChannel": "Parcelforce",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1823": {
                    "id": 1823,
                    "externalId": "2015291000",
                    "application": "OrderHub",
                    "channel": "royal-mail-nd",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "CG OBA",
                    "credentials": "hCrj92WJJBP/ZwLM2YAsOUQpIJgNNHR1miGevkZ+ojdpUQaP02BEdQnku/fS+aZPgK+iCw4eoJuAD+2qY+FuIL3BC2jlfZCvlJ77R+bke8Mpp3/iUSFCenaWyxSbCpId4AsgilA75jx6vp9iJb/JfJ4E4ptVX+xXKkbasftz6zahmX9ZPGyQ5xF5EuPoT5jIPi+1Nrn7NwczmdUgFXYELPjeVCV3Mu4+Fktfd5u15sL0IN8N221wSo/iXsdEb/JkXxwiyyNaUJpplsdrRTF0tMRaHj5iz8NbxjzL/q7DZh5E8zHHGcCbWoQ5ZdRaUWEA8W6qF3Snxk/Q7KgHdmFYPMl82/MFAuqVgJN8JDVKSGpoxdB6Hew4iC1cibJOJYJbyTS/j8VBiAOex5jLRjwGpiX8cK+7tsWRdhcP27uX8SgZqvvBImyH9kp901/V5HYFiDGVJtd8j8zNpTVngEJ9szBrFVKQSrOvob4ZBCOLASpNP47CrYYmcYXcuO0hy1jGvSL7FFXZaYGUXFcZsZ84SxpgZR5GjwPqF0MctEs85xw=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-11-16 15:45:10",
                    "stockManagement": 0,
                    "externalData": {
                        "accountType": "both",
                        "formSubmissionDate": "2016-11-16 15:45:11",
                        "domesticServices": "",
                        "internationalServices": ""
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "3086": {
                    "id": 3086,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "ekm",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "EKM",
                    "credentials": "gwdBr3TbEXYietikBI+mOpc/BS8iOF8h5kCjQkIjHJQteqPeVH7Kpb2PHH8gA4bUD+nu2YnmfNKW8BNqRfvrwxX0jGIXBEBpk/BXbyvuu0KuYyoguq6K3iTbaM7awC2acUBeK5SpaRSnGYB3zODVtFY/6neMK9b5fQOhyWm2itMphSkEicN/9g6z8/Q3myo/eT7Wj1yf2SaeyA1zrp+MwzrbiVt5/800uYARkIvqqu1dYQdKpKcuHH3a5GA6MLupbPB/CPHldaWGnv2kIdNWiWz/6SVSJYI7jmru2Qnvt/mdHmFHjXXOvNl0b/bZoQaEYm9xwCPC6+14hS4bsFnFqBqoaDnl8/1PmPXoOofQ9WQI6Tuhncu0xVJIONdIN6zhIpCtKK0KylBQ5OBnusHFDUhy3F5WFX+n3K6+WbVWbNWOCqmDOdePsCWM9pTBFvpPJkmHeDeuKfu21by9Gpc1KnnwKdUcmWX+X+8kO2m0mgs9xOrlJ7+WC61TQe93w5/QIIPRmC+CjVhOawZwg16M9U89k26aQMoEWAr5PA/MLIlElw/mVdlNwVYig18fh9hPBlJMcpHh5YTFUosyj1pP7fcDImmodxyoH5GbF2elwB11sfNyALsNFvz7mDBJt9Bec3piOaS3mCGGkwbbuUMSWhbpX6Gd/7hC/ZCp0lvon2AXt/pNAPKZYsPURnShF/D/SbVMF9qDFO0Fd6o8dwsUAPFPIBvDl8MfBrqs5VKa07BqorFZ49QiOuR5jTKDAWpY",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": "2019-05-31 22:02:04",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2017-06-26 15:12:05",
                    "stockManagement": 0,
                    "externalData": {
                        "ekmUsername": "channelgrabber"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1560910068,
                        "processed": 392,
                        "total": 392,
                        "lastCompletedDate": "2019-06-19 02:13:15"
                    }
                },
                "3169": {
                    "id": 3169,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "ebay",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "chosen12010",
                    "credentials": "UA2QeZxpw9ep8cbz5gSaMPOI2S682+fB8XnO7q4128yBxcp2d6tlGt2M63SuwTLOnC+IZv8K4nWetiE4o5APCqm997qiPh1GnrNxcwA5b5uhhDOz2jFcSuJ/F6Yl4QjDQLgPTGwTuoVnXob88cO2d1SLEpHkGjXEtR2XOMi9RNW85FyfQpUtzdO9GmSaExjNiGhPGYeI584qaMJxCbAQ+CPLq8i+9Hyg1L7lwdPONSVsDGruBrDX3JeAXdSEV+MD34HWzyAWqqU2NePseh/zr9ZVLBzXj4tKq7su0yrpbq6knM8mCzqZKom3zxeC6rw7R5RCHDrmP1HkzvS0PpGjWz3kx6/9GJEVIMMWJ3JqglQDiWoBalXT41lbtxFOuI3140msja1qdmavPesf5ZAwHq2ryCCtl7DlRLYb5m2EJyH/mPYS0XP+f2n+DgDNXtQOtclPyR/olO68VClw+AoQHSDnqyh3Zc4WjmaLhpsDUz/5PBwp+dm1NQhTlzZiEXk2RDCScILu2ZPddMOclnrn2a4QXwQGag8AMlq7p4sBMU9MLvO073YdrzcVvxNaXnxAoaIZ1WD+W6OdA4SNjdmlNcB2gR4pNjm88paG6kEd+SGGgTTVddA/fee5kS0OtI1S/ydgGqjLsPYIJE+kTcEFc1uAOkyRVMNTN3T2zwjeleRsLTZ4PkXABDokhmO/r0mo6dxjThId+xA7Sq6Jg7MP+Tu6WveS/UbjQrtC8NicbTgxPzu5Xa9rCcK+/HyC4zwdFaCvC3MQSaGkn9MvsRcVKQklQlOISZaILV10B3/4YM5JsDmDIZr8V2hYefC7JuvVXctaiGDRbsw7Ju58vTi65dNXA0myisoNDR0cai/EvNwHszYzaocCdX5af7NXaeCxX+yitu/J5EPMmDDEiFAND9Tsf1wf/bF83bpalEpKsAaSnbvn5RE+6M8xC+oiW+At8zBLEK8SZb4fzfI5sj96Eu1qmpHTYnAUxvCzcHZmnZAlfmji6t0EfxSe0NFulzHKPPcuoUzgFFofrBhDaDXBteqMFquufFm7+vAp4XsVKgA0yAVdfi2U6niUhhw3G4D5zDbqyoKAkNDS1gnVK42sAAInZQGimQo3xO+I3nNECsbg6eFSWXwHkfgAnAq+neVZjcYm6o+qWv8To/MSw6hWE8yJc94JDu4nGKUFLlv0xE4D31sozgIYDhLggqwTAYyVIzNguxEDBo2t3cCcI1UF/+dgKXrZ3wiV4YWZJJxz9MKcLFm80P/2RITcQN9W3eVDN9wX0XPJsMYNyJp3BTbCdifrOk4hoF87oI+IeM6369uLIq8LN0x7ZHM2+GrZfXh8hSBiQnV9H19JI32/45QXaR0TU1BGVWXENy++mudI8F1ear3PDtUvxIg1kM+qHNaGah6braiv6XkYRLlH6b9YYk0BPQjoCwxyQ4HIzs5XLhr4lRoXA1rxuNeMRwV5tT6gBhsELw4vdVzKDDwhBSPb0ei/cbqc3oj9iYppLI8pwGoCQn/vBqDJaRJHoJcL9ZwE4CXFqgegU3arIGXqSFvxqlXQX6Uu+da371pu5MftsABT9j1yJSrXvfGxQrISF5SIz8gzgSHa0o5rZweOaqeq613Gwvsi8lpdMwCUX/kTGWArxeVZjySm/g28fvUgztpZGkAKcCVnSH2bzvLcvaqP6X3ezUAdfG6y+Lv/dHz3ZUFBTRGU+UN3JYDcL88vCkKrrOrsiXSVlgpo8+chOtnKjxnJFYnIWvLj4+qCBcD1NPw=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": "2018-12-27 09:19:37",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2017-07-05 09:19:37",
                    "stockManagement": 0,
                    "externalData": {
                        "importEbayEmails": 1,
                        "globalShippingProgram": 0,
                        "listingLocation": null,
                        "listingCurrency": null,
                        "paypalEmail": null,
                        "listingDuration": null,
                        "listingDispatchTime": null,
                        "listingPaymentMethods": [],
                        "oAuthExpiryDate": null
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    },
                    "listingsAuthActive": false,
                    "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=MzE2OQ==",
                    "siteId": 3
                },
                "3170": {
                    "id": 3170,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "ebay",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "eBay",
                    "credentials": "YnFTJPtcN2vdvLi3beaLxVmry6ZRq1g7FaltD6+W0fYrDGon1EY6sPOLqIjyUz7LfDd4GSfFV5X3svlWdNQyFNqWO/0nvF+Hte/I+wfv0C/mKOYW3yQ2cfdiTqAcsTkAFZC7gmnS0f42KDKntDyqNqLYvfoMH60r1f5z7GFTlIeeJw0ewRw+Uw5TmveiAcb9Q6NPSycQNxnK7zeAOUvZZ4bsIVWdEwS6wX7K7oP/zjMlSdPau7+E6BjrqkuyfNIDS8Tn9xKvwmigNn2yu1tp8WbrvXXonuAlBYZcmVX1nXwdvw9sMyRQbV1Zm8HB/tR9DloBjadUybCl/dlWFWovHd6xA0d3vgXLDOVF5LpBPLFlOmaAKp24f4Aw35vR8qMW03A6+s8jJvdSBkepwrvlNTiK3RfAx7np3Z9aSBA6P2BVpUrXuUvVwFI30Ub7jLBmIjIIyTsHOIpiM+XPBrQv3g6sdm2+oPh/5k1F+M/6ZAM5Pyml+lgnqEiGdF54EXT1cZiosVxcThC8Z5cg2XmbdU2ZHqQwhArbzZ59ne1moullk19yGywWK3JVeGhy87CHqyyJGZeo1MB/DAikuW0t1Aozage5nhGfkiBzcsrRT29PVyFMGKMWCqNLJQ56dXkllbwd6HuKsxZwGTwnaqJJRWE8bRpaytOjAs9TyUA8Ojo7/+Y4T7ozK3kbP7RMrPeLFFM4rBCCJrsjaDHM3IQOlAQr9AbpOwrb7faBtC22xdXLW3l+WDo+EpulNQ2gNdiyMO9pBinfARuW9UblAJRosEUsw/tgFM9rz55YwVFQpPaMFfJe2EVVWiXsbNlIowvvDARBu2CDm9Ti9my18LHYkLq66NnKiqSwiK1r9fT8jw3nb+UtdTvLXgRIeYCkGkxLcUUGiIdtcbPLdC6U89kmNjcnoTyl9gJQ1q1WzzVGI8FWIy/YLJBGTTRy6728mFnlWrPE6JyCDAidb4V4RE5BQNLFJIY/bICprRoLNUHrIbhjiujhcU/P12NpxtY00r+FdAJmxMO1LnPl2QnNsG7pfEu093Mof25j/NkT973TwdrO/yOsd8sR9KxhzmzKHGl5l3Z2QDyMO2Kc4/mxwUfm6J1Ns/Z3K9eWjLDntck3302oC1Hcm7sVTx8xJ35sTX8VzvBBdspWsavyDs1fCfvwSKhHK4R1zpTTh4a2ZEJV+M6BroxsELe93/3mwTKHhVKK3U+xsLx7LevcRdDpIo6rcP2wylQeyRXsw+d+tw6bb7RTHL3D7Mt5l3dWDpV3KyYGzEQZao+2lm224GvtZd15Ey1FCOBVi/ks0+VYK5bOaz/bPLNDVqCW1deOujg6V0kyHm6Iy0LROngS38G2ZooN8gEaXbUdE2muq7ORC4yXgs4diVPuQo63OubPHMLctFNU/LdgSjXGFyGbzc/TBMy8nxMsijPtVbBCt3A9oUFeIubRLlP3OkXmjPrnNiLQT3NUABFOrDiIH+6DA3fNu3+8o1JDqWcOKvQhRwsRXskuJ5WSpEW39vg8M8dO/F3V6uYe2ET9t2bQhis1CuZmEFo4EMghZZE4+6pco3v+wCTi4plbk/Hf0f9MNUHA8MiGMkCW+ZsNZX5mV24UGbGHcPG+D5LyNPIeUYJTto+yomDArATe+xY1m/PNPgftBqEngESjbv06xXwL4NI+74nch7KDFuWyLLGmZxsisd6u52jybidgkjJzUFrq4fPRvDt8P+XLBjloFlBovlaOjeQGEqJ+2nrBvGBFyvYGrUxiOmXab/6B1o514kFuqz3hQeu4UZjy8Wv/Or9vv2KobqNG/SQ+9Q==",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": "2019-06-10 13:30:29",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2017-07-05 09:22:16",
                    "stockManagement": 0,
                    "externalData": {
                        "importEbayEmails": 1,
                        "globalShippingProgram": 0,
                        "listingLocation": "Manchester",
                        "listingCurrency": null,
                        "paypalEmail": "accounts@channelgrabber.com",
                        "listingDuration": "GTC",
                        "listingDispatchTime": 1,
                        "listingPaymentMethods": [
                            "PayPal"
                        ],
                        "oAuthExpiryDate": "2020-08-16 01:02:32"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1560132005,
                        "processed": 0,
                        "total": 0,
                        "lastCompletedDate": "2019-06-05 02:00:15"
                    },
                    "listingsAuthActive": false,
                    "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=MzE3MA==",
                    "siteId": 3
                },
                "3243": {
                    "id": 3243,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "amazon",
                    "organisationUnitId": 10949,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Amazon EU",
                    "credentials": "7KcoJhcMl9cT5rTLVAL58CRe2hSr87TkfK9zdFb2urppxk7EPnDoisnizeXmEKMFeYlaBwVoYJG/J8rIB+MwSKJkisYeQ3Az/egy0uwOs3UE7Z/UunXuA5xMRIZLI0GkzHYH0DW/67U6/zrSNdNuJ+QXgQLiKIVp93xExHup3yGD0YLHvxNSw58/RoWrRLpL+TXpT6X9VHfigokGHrIu4uwRnyByUyQHaAyeUGBxItx/pbeldHE1T3LJoY8JiGkoQwIUYCiE4jqoLHCwXqzZ9pWQG5LFNiSaGkA7WmOmTsWZbymL7Mg/YA7DzyjuFXS1mfdDFCRN512M1fshjF+X4psCnutqb+k8UgBvEHGedBJtQrfecrFRmkovlWD0Po62JCzpJAGCKIQ6IZqioKZQLEhD4J5AMcmx+CiVHku52/5WvByALEWvk20Mu4nthBHMnrc8whDd1HNzL+Idj6r6PaebVX22E1mb8w7pUUcu20lA9LNwcBoT4VDHHXtO5nPwD4vsPAUykf5es2vuEHZfhXDN4kdJ2s0ZS85dqxK53Qc8IsI19Bifb0Z4ofOFhFApFJ+8Bn6/Dcm8KahJzH76F2cj2bSevXflDLtlbSzQjILyYP9UifJncZLriu6mHKv+b4oshBCzIl03MdWXgoLUrHhgOiGB1jctzTp92TYX0iQwomQDHudS56ZaGGDBMlJtED3F6ojBhNBZQy+vIeBTcbIBgs60H7PgAjbPACtt/nN4zlw/eOFQfUEyWW3g9lqGPaIGwZJFUZeFC1F+DmfyReyoOqOaHhe9HRYWxFVx/QDjTZReHTHtSFIsAVX7HH8lyH8XnJqVKqdIztJWmikcoyj4Djj/LtcOFlktZFRHtZZFZW9i8/gKWj5LuA0QhlwgteoF0cY8KNMqYhpdghRggN3mU66tiXl0hSHLbLsPSORRdJgi4esMT7jXzvFVXhllCqycx0EkrIbnz2HTndq9b6lAdDTxxLIEPcdObHnOAPLPWxlmrxzxxC6922DqkqlK9bmkKtrTHDuN1DrGP2p+Lf3mdrTGoskYwVJ2x89mR0QnnQtQM3jjXst/ujwZfRKtEOMNx1Cwq3uqOSsCTt434A==",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "sales",
                        "shipping"
                    ],
                    "cgCreationDate": "2017-07-11 10:47:32",
                    "stockManagement": 0,
                    "externalData": {
                        "fbaOrderImport": 0,
                        "hash": "3e474f1d119d973829736ed9f7e18a699ff607bf",
                        "originalEmailAddress": "",
                        "fulfillmentLatency": 2,
                        "mcfEnabled": 1,
                        "messagingSetUp": 0,
                        "includeFbaStock": 0,
                        "stockFromFbaLocationId": 2796,
                        "regionCode": null,
                        "marketplaceIds": "A13V1IB3VIYZZH,A1F83G8C2ARO7P,A1PA6795UKMFR9,A1RKKUPIHCS9HS,A1ZFFQZ3HTUKT9,A38D8NSA03LJTC,A62U237T8HV6N,AFQLKURYRPEL8,APJ6JRA9NG5V4,AZMDEXL2RVFNN"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1563850808,
                        "processed": 49,
                        "total": 45,
                        "lastCompletedDate": "2019-07-23 03:36:47"
                    }
                },
                "3250": {
                    "id": 3250,
                    "externalId": "2015291000",
                    "application": "OrderHub",
                    "channel": "royal-mail-nd",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Royal Mail - NetDespatch",
                    "credentials": "TPAJ661Zv8weflgLAfGnxOUuidrIqYz7h2VF+DXL62F97ORyRp43wkY66Xf6AidA8MPWZNmo3QiBHLP5T7/mOZ7v69O1wf07NGm/1G9TvAH8RmJXxSmk069W9MTANKOgEkaKQYpSEyRG23qfYHx5bHgg9gM9+ljgEbbfpwVJSIMo0Ve18dFSGz28O5y74k7tcmbHyFe5NWjR2imIEkvQ75Ph4Dw6Xj2yY3d5W6sc4EjiRAJ7PH/01xkQFGuVkbFIARVjk8qeMnY9qOjuqrWoRUrJEpldvcuTj6VSwjtsImEDc7S8vcBuqtuLHQACUqi0em4OSOsEUa6Uty7rodNGhArJLkhmHX5KMX+tjc+tqunrHgTk1PW4OP0gqJx3PqFKnS/DWlQ1DzNe4/OhBzwQF2+zi1ovdVJCtj5Bt+L1fpYGad82rwHa2j6mTTnQXPNGaa3uBpXKDALBz2/s/XZNIXElriH1/h+UCMupDfDiCiDJ2SeHU8J9HnraiYswLVmlUqL468lqoL/9ALeqXeLzIW0LM5lFz6df8cpZtTCV0Ew=",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2017-07-11 14:38:55",
                    "stockManagement": 0,
                    "externalData": {
                        "accountType": "both",
                        "formSubmissionDate": "2017-07-11 14:38:57",
                        "domesticServices": "",
                        "internationalServices": ""
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "3252": {
                    "id": 3252,
                    "externalId": "3252",
                    "application": "OrderHub",
                    "channel": "myhermes-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "MyHermes",
                    "credentials": "7f0kv+R/TAgt0FTTQa/jAgjjqKeSJJEqscqyXH7V3jN1DE4phocq1MLfFFYGJ0a7AqSagcRmtARNBXvSRlw3nzboLdHgfTKdaOybiEmDmID2zI1cmpNdhi3h1wPelhCWOAkoPGPSCXyndPc0AzVDWHzRte2v76B5WJM7+QuVKgxxELxEMjub5BlN/WbQhjho/rCSTfQPW5Dahflawhb8eRPGKgFq0IdymRAikIXylt0ofznpXkIgxdiqvg9duxViHxmPQ41643IDrsosKt41Bm66fYg4e2WlU00l9ryf7upXbOlhKTFpvHDEDBy/GpDIMp2uHWf10fz1QzOjxdK5YIGwJF2mO9br6rFoN/Y4AVc=",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2017-07-11 14:43:26",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "MyHermes",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "3336": {
                    "id": 3336,
                    "externalId": "3336",
                    "application": "OrderHub",
                    "channel": "dpd-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "DPD",
                    "credentials": "gQR0bS/8kMl1ZdPqxuByLRgIzhL8ifWeqzS9R90xxZP/0lQR5ZhTPJHxLRD7FfS8PLepaPkA34Oix82txMxXWBzj2oWxO58d6+NV8s7QjYwo2Nt4TcljqCBUa2q9ci2bXAAEaFr1Rmnn/Q/DfLyS0NWbPbDjknBP0+//MK0BHZaHU4sQlMTe621Bor8up9S4jQZpUJpc7uksyCJwxG8LlhzNLlOIB7bov+KWx4zfUtKs93uGnGlfoUXylNVFCCwqJBJkyB++HMZIY9HPslnQ4doB8U8zwTV0zcu3hUdCWahbeEPSR8/zIOQn9GyOftzEWqa/3qB6VwLkg1DbtVU8DyCIbhcrzLaz9sOkl9XnMid+ZT5Gp3+w0auL7svxqiKSjmDvG81uifFbJxZL23Xk8EBpl9Sfy2/kTwpVlOlB4sy0Mm2zN1HeiJXb54tPBt7plNPYFWtxF83Ij53uL+cDpPzot8KqlK4DPQ92Yr6xRqoAqY1Vsw5okylz2k48Rw8q7sipDQYuza+A5v8LMjGVQzpl/gl8rh/WtcNxvyL+D4vSWafxx7GfIglUfschK9EZgxp1pC26UWBt7B41zQQIaSqKRmiCKkN8ZxrbQ5TyHUEY8LlZwJBwGxEye2qW6mL2",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2017-07-21 14:20:27",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "DPD",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "3337": {
                    "id": 3337,
                    "externalId": "3337",
                    "application": "OrderHub",
                    "channel": "interlink-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "DPD Local",
                    "credentials": "VsgnLNrzpX0lbxDf762CWR/zHY4J57fFZIPPMuHBWSs7q1woeJfGIhJNV3Q8aJufgNlA6g9ipRofQjMMT1GcGEcus5TQYzLrMag3PekIzx/LeW9HxYeJlS6+mVJ6pNki09+ePPLJfUhkZ0DgTghmh0Vk2/0Qst1ur9IvRmdjNoqDtCF9avYJlq3q2nxxf8erS1x6QwMe6h5BM4vOwJc44bt92/ioK6q+KaUJYrbEKDTv+X9skJuACyDVkrdC2o3KquD3ie2hKWfQ1BeXhlfId1WmO0KXFKZpn0bVAIsYPadjlubUmIxBw0IZ2vLMkkVQeJnmIIdmQ1ZDpD7YvZWNN/wYzHavIBa6+0UEeCfr1AlSw0bhg/F/ZWn4j77njvalqMbDQjFaiTALO2NofS3htvF4RQZVwEYXEKwtYZyaiRKotIY1a2tYAWOQmVJOluTPZfwD3WTlvB26xfE6ENiK7sgLiE6/dbrN4xTjAsTAB6d2Z0Hxm1Oa0xaUIy0/Pyg9oUUCkwzAlyP/dpXbtMUQiLbJxJg9uVMLw44lBf8WXVz5KcJK1/Hw8ZDfPWMz+dIj0uInuaA0kVyxz/xx8IUKSf21a03DibXKRGKDM0WSlhAO0+1gSqPQPLkw+srgAg0sRmzIXBk5rpq8opXd1QLv4ugYvkGs7rh+OU7mcl0rNIHIT+m8Hpn3SDeW66PYhnxqtmX2PAJ7K+jDQaKxmCguEA==",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2017-07-21 14:42:40",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "DPD Local",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "3747": {
                    "id": 3747,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "royal-mail",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Royal Mail (PPI)",
                    "credentials": "Royal Mail",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2017-08-24 15:38:05",
                    "stockManagement": 0,
                    "externalData": {
                        "PPINumber": ""
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "11660": {
                    "id": 11660,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "shopify",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Shopify",
                    "credentials": "2nfg+u+z7qgiHaUqL7yEp5wBIVm2mDW9bW8IRa/tbJ/NVwBsRfGfZD3QNc4CHhkjidsA6bUMGlGTIcVhdvsB+yEecd65eRhg82xhJ6Phmwg51zsVENmCtRvuQ2tjJGpibW3M8gGAW4IJ+5eAdJbvG9jT9+OqlLLGVK4FSZ9+iQoHjKsQ6DqoQd892BOl7dFkcKLmSbKEAXQQXkRU0D9sMbecSmACoa0CSfBCGTEqgOE=",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": "2019-06-26 12:25:37",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2018-01-04 16:40:43",
                    "stockManagement": 0,
                    "externalData": {
                        "shopHost": "dev-shopify-orderhub-io.myshopify.com"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1529671637,
                        "processed": 1,
                        "total": 1,
                        "lastCompletedDate": "2018-06-23 12:47:32"
                    }
                },
                "12354": {
                    "id": 12354,
                    "externalId": "47fwg8cpdt",
                    "application": "OrderHub",
                    "channel": "big-commerce",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "BigCommerce",
                    "credentials": "nYg4OGzKeMmDeVdlbKJRBg3WLTlxIWOK0or+WcNloytYBe91o1iu4DDfhTuCiat72+b+3G8yCA32SCXdB2+mzBxnW2mkSfUq73gHOKhQpNkBiA5dT+XYfHokcdHJErxWps+QtofTX0zFmINbvZOvmAvPpLdUblNMW6DI7IJ2wHoGFlo0UjB4b1PIZJ3HbgDZRD3RQWCIHbXgxpuOAKAJx6dHVtnx6hxqVGMrXB2CApU=",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": "2018-07-30 15:12:37",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2018-02-19 11:20:51",
                    "stockManagement": 0,
                    "externalData": {
                        "secureUrl": "https://store-47fwg8cpdt.mybigcommerce.com",
                        "weightUnits": "kg",
                        "dimensionUnits": "Centimeters"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1521127671,
                        "processed": 13,
                        "total": 13,
                        "lastCompletedDate": "2018-03-15 15:28:15"
                    }
                },
                "12355": {
                    "id": 12355,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "ekm",
                    "organisationUnitId": 10949,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Acme",
                    "credentials": "Xu6vYvSef2w9DAA3fiZvhLYknLkLPyN9lGehw++yU23TAPfkgFESdOvWztrilx4/twu7etv1q/3xrxpQ2ZqZ6lE9MqsxovvQCRYZRTD2anY08cuzlDNJ5Xm7cds/SPcuA0bLkbkeO2SFgVqrc7cF4fJYfO/FLQOY878LYaTvFJL9xT8jx93gzf8TGDctB1IABpkLG3kaZ/7t1gD5adBukAbhzu9CA46r0YyqU4rDqFTGDS2BVp1z/p31ZFTElA42nRsHJdoJ+Q/ICfjLfD+NlELsRWne1dp4Y0x7FzZ6djfcS/ZvtWoPexv7Xz0VcGdz4Bz9odLqI50TOFJ+GPVOeE/XX9k8Hk9Yx2P/j1R082HZpK/NZlKdCD8ovh/g0oO4dSaZNYpKoZs3uUWogN56GvpQlxUf1CtorODaTTwBPPbSpSAEzzbtMfzJJSdbot6lM/hOyurtuwEAmao+V4jZwX7Pqq5DqrTXVZcHTtSUZQ2BXOP6W0ug07rcnXAXAd9dNcmld5d6ywHFhO1U3osZYrbat5niVRORBdmAu3842KhfuHefTnZ9D34H2YpqcR3wVsEd44oPgSY3EJU9n/lvUj/Aohn0Oz+uoOXlXPCiNBl1wQQ61CZSRsQqCF4tWgDdQsUb0wEJJaUSJ/JcnFBiZyEeTk5TnghbxhXSpSvuDcBNgh2cRUhal0mf/8+zRY8rEymTDzRxXoUd9InS3YJS64dfwJvJ4OOmq0bI1BXlMXs3IX/XAzS/Cyj8oAcw3eIx",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": "2019-05-31 22:01:11",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2018-02-19 11:37:54",
                    "stockManagement": 0,
                    "externalData": {
                        "ekmUsername": "channelgrabber"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1560910676,
                        "processed": 392,
                        "total": 392,
                        "lastCompletedDate": "2019-06-19 04:34:37"
                    }
                },
                "12628": {
                    "id": 12628,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "woo-commerce",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "WooCommerce",
                    "credentials": "n74zEWIEcNi2aN1IiseeY4EBTnZxbT+pIt/V6XmdNHJSxK/0SPaiiSWKFbV0qSJtk88YwMaTf2wIgN+32hsCPIertpodyhhccXY1D5C72z07zEe5R48fxaRtPOcZDdbcwQzKbdz6qMta9o5ZyAlsVqYmezGrQ+tugX0sx4wACg1caskGqzGSjsrXMsSzTeG5/S7B1kT9qhXXE7vGBks03Q3l1RK2lbdd1ilO1WorAQZVtZuCugBuPuINcADQ7RhkqloG7UALR5QuF3oTdofh5ZrqKwx8c0FCQErZKn5El9iWO7NvgaHybiizYrIPDUoAacRxpJXx8Z4BjlSrItmwlIMC1XPr/jzOh9CVU/9i0Vo9BkoOpHGXP0ykzP2fHdw1hRaV3UbnEe7QnR5Oqf1t5wdfCNOuVEG3cNTJqSY87l+6XBtN0918lq1vT8p2A5n56GuKMRsuzrb5afgNAfDmXA==",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2018-03-09 14:20:55",
                    "stockManagement": 0,
                    "externalData": {
                        "decimalSeparator": ".",
                        "thousandSeparator": ",",
                        "dimensionUnit": "cm",
                        "weightUnit": "kg",
                        "taxIncluded": 0,
                        "currency": "GBP",
                        "sslEnabled": 0,
                        "sslUsed": 0
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1555459231,
                        "processed": 92,
                        "total": 92,
                        "lastCompletedDate": "2019-04-17 00:02:27"
                    }
                },
                "12917": {
                    "id": 12917,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "royal-mail-click-drop",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Royal Mail Click & Drop",
                    "credentials": "FyGQTsTo6FxxEgfZP5u/mg3S1GYllTb1Fy7Rs6Va50hJKMOPelFoKHpygFXmXHysCbIR9GpAjIdCpopxwHuwvFAe5o1azYz+WlSKG8VGPJuDDFPhZ2dFPlW2s8DScHpjFO2TnH2D+7DmauR1W/Ttm2v8FIoWY2Go7+S+GE3fq1wZmf3eESt84Dn8hsx39lzz",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2018-04-03 13:25:10",
                    "stockManagement": 0,
                    "externalData": [],
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "14098": {
                    "id": 14098,
                    "externalId": "14098",
                    "application": "OrderHub",
                    "channel": "dpd-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "DPD",
                    "credentials": "uUXAEpiPi895LUMm4TvfhhQDFNZUt6mMfjkRyqEKtBODeuPnx2f5EmQUhp8AQmYbMbyM6B7amdpHW0alXAG7gt/ufw1Ndhb+iK/q8if8V6pNK49HGba7f1jY5/0d9+QEN0TAeadR0eSZeawW1BweyvpGx1b6sLF21ejgecRKHPtFPniv/Ym6EO26D9OSyyZSIZygyintBBX7r9fnCiCA2BRk/IR49CrdYTz5jeEubd8ARsY7MJXZjE6O6/TePqYKzXio4Q+GnA2i4Tc+dt9WbfaiRHQjpT5daot6wDEWeIDtm2fe4w+f44uuitY9S5zSVdcMFO1Piu7LPk6ohoebtoK4OQUZpQVJDMNlO8vgX4zbgT1GxYzDx1FdVtniKnp6eZbt3rp+2h3WNbN27w3NoMWQa9Lo9SHaz6zHgnhTveTFQ/oW142T1n3wEzE4qKAurT2hOix61b2uUO0wP9R95b2v0ryLxLRjmI2KX2pp08UBa9VAgAh22fI1KhK38LVigCE+0doPUcEnF0FeogihMdtC9ZFRP13az2+sfw/p+STwbCpujEdfg4qpDLRAkE0saEUVBBpzSA4ipunEm++PrsqDv2jXlMGeP+ViUD+m/BcW5von+M/d3q9JsjT7hzak",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2018-08-23 13:00:52",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "DPD",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "15504": {
                    "id": 15504,
                    "externalId": "15504",
                    "application": "OrderHub",
                    "channel": "royal-mail-intersoft-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Royal Mail OBA",
                    "credentials": "cbwMXR2z3oeu91Jcqi8PbLZwsbe6mRSmKotJNhSP740WFyH/xN+I8AzruE/RgDS0urTeMdRLX/EaaeVaVszk/SnJVyih4VJyOmskqrzzGVrA5iaPGM1nLcY4rwzZZd9DFNLYqEoSbqgJ141orsmCLVL2ApOLY7SpfePZywCmFnKlTJ3FDsSLix4kX0wjuIWi3QJYAMFahOrb6RXR24BVijZ/x9mniiKUBkzJZccDuvGcpRLcNWgnl//d/Hspnum7fmCjhcVZpeKcTjyCPlNKyrbwqtYYCyiADvh4zzQt1rFPR4DlSE6z87M85yPTiAr/1UWTohXOrE/vOPSZmxw5cOg6Y6weDvi2GDtu4CZvR2+Q3k89NyyAIT/VLGMugWmJKVf+aibk6dpd5E4URa23CA==",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2019-04-18 09:29:14",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "Royal Mail OBA (In)",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                }
            },
            "stockModeDefault": "all",
            "stockLevelDefault": null,
            "lowStockThresholdDefault": {
                "toggle": true,
                "value": 5
            },
            "stockModeDesc": null,
            "stockModeOptions": [
                {
                    "value": "null",
                    "title": "Default (List all)",
                    "selected": true
                },
                {
                    "value": "all",
                    "title": "List all"
                },
                {
                    "value": "max",
                    "title": "List up to a maximum of"
                },
                {
                    "value": "fixed",
                    "title": "Fix the level at"
                }
            ],
            "taxRates": {
                "GB": {
                    "GB1": {
                        "name": "Standard",
                        "rate": 20,
                        "selected": true
                    },
                    "GB2": {
                        "name": "Reduced",
                        "rate": 5
                    },
                    "GB3": {
                        "name": "Zero",
                        "rate": 0
                    }
                }
            },
            "variationCount": 0,
            "variationIds": [],
            "stock": {
                "id": 6945865,
                "organisationUnitId": 10558,
                "sku": "EXBLU",
                "stockMode": null,
                "stockLevel": null,
                "includePurchaseOrders": false,
                "includePurchaseOrdersUseDefault": true,
                "lowStockThresholdOn": "default",
                "lowStockThresholdValue": null,
                "lowStockThresholdTriggered": true,
                "locations": [
                    {
                        "id": "6945865-464",
                        "locationId": 464,
                        "stockId": 6945865,
                        "onHand": 2,
                        "allocated": 0,
                        "onPurchaseOrder": 0,
                        "eTag": null
                    }
                ]
            },
            "details": {
                "id": 1888937,
                "sku": "EXBLU",
                "weight": 0,
                "width": 0,
                "height": 0,
                "length": 0,
                "price": null,
                "description": null,
                "condition": "New",
                "brand": null,
                "mpn": null,
                "ean": null,
                "upc": null,
                "isbn": null,
                "barcodeNotApplicable": false,
                "cost": "0.00"
            },
            "linkStatus": "finishedFetching"
        },
        {
            "id": 11409247,
            "organisationUnitId": 10558,
            "sku": "EXWHI",
            "name": "",
            "deleted": false,
            "parentProductId": 11400129,
            "attributeNames": [],
            "attributeValues": {
                "Colour": "White"
            },
            "imageIds": [
                {
                    "id": 13812565,
                    "order": 0
                }
            ],
            "listingImageIds": [
                {
                    "id": 13812565,
                    "listingId": 10222599,
                    "order": 0
                }
            ],
            "taxRateIds": [],
            "cgCreationDate": "2019-05-04 15:03:15",
            "pickingLocations": [],
            "eTag": "1f3c6c66129520b0baa32491555e183f73b5cbff",
            "images": [
                {
                    "id": 13812565,
                    "organisationUnitId": 10558,
                    "url": "https://channelgrabber.23.ekm.shop/ekmps/shops/channelgrabber/images/excalibur-stone-not-supplied-103-p.jpeg"
                }
            ],
            "listings": {
                "10222599": {
                    "id": 10222599,
                    "organisationUnitId": 10558,
                    "productIds": [
                        11400129,
                        11400132,
                        11400134,
                        11409247
                    ],
                    "externalId": "103",
                    "channel": "ekm",
                    "status": "active",
                    "name": "Excalibur (stone not supplied)",
                    "description": "Wielded by King Arthur!*<br /><br /><br /><br />* we think",
                    "price": "2.0000",
                    "cost": null,
                    "condition": "New",
                    "accountId": 3086,
                    "marketplace": "",
                    "productSkus": {
                        "11400129": "",
                        "11400132": "EXRED",
                        "11400134": "EXBLU",
                        "11409247": "EXWHI"
                    },
                    "replacedById": null,
                    "skuExternalIdMap": [],
                    "lastModified": null,
                    "url": "https://23.ekm.net/ekmps/shops/channelgrabber/index.asp?function=DISPLAYPRODUCT&productid=103",
                    "message": ""
                }
            },
            "listingsPerAccount": {
                "3086": [
                    10222599
                ]
            },
            "activeSalesAccounts": {
                "3243": {
                    "id": 3243,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "amazon",
                    "organisationUnitId": 10949,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Amazon EU",
                    "credentials": "7KcoJhcMl9cT5rTLVAL58CRe2hSr87TkfK9zdFb2urppxk7EPnDoisnizeXmEKMFeYlaBwVoYJG/J8rIB+MwSKJkisYeQ3Az/egy0uwOs3UE7Z/UunXuA5xMRIZLI0GkzHYH0DW/67U6/zrSNdNuJ+QXgQLiKIVp93xExHup3yGD0YLHvxNSw58/RoWrRLpL+TXpT6X9VHfigokGHrIu4uwRnyByUyQHaAyeUGBxItx/pbeldHE1T3LJoY8JiGkoQwIUYCiE4jqoLHCwXqzZ9pWQG5LFNiSaGkA7WmOmTsWZbymL7Mg/YA7DzyjuFXS1mfdDFCRN512M1fshjF+X4psCnutqb+k8UgBvEHGedBJtQrfecrFRmkovlWD0Po62JCzpJAGCKIQ6IZqioKZQLEhD4J5AMcmx+CiVHku52/5WvByALEWvk20Mu4nthBHMnrc8whDd1HNzL+Idj6r6PaebVX22E1mb8w7pUUcu20lA9LNwcBoT4VDHHXtO5nPwD4vsPAUykf5es2vuEHZfhXDN4kdJ2s0ZS85dqxK53Qc8IsI19Bifb0Z4ofOFhFApFJ+8Bn6/Dcm8KahJzH76F2cj2bSevXflDLtlbSzQjILyYP9UifJncZLriu6mHKv+b4oshBCzIl03MdWXgoLUrHhgOiGB1jctzTp92TYX0iQwomQDHudS56ZaGGDBMlJtED3F6ojBhNBZQy+vIeBTcbIBgs60H7PgAjbPACtt/nN4zlw/eOFQfUEyWW3g9lqGPaIGwZJFUZeFC1F+DmfyReyoOqOaHhe9HRYWxFVx/QDjTZReHTHtSFIsAVX7HH8lyH8XnJqVKqdIztJWmikcoyj4Djj/LtcOFlktZFRHtZZFZW9i8/gKWj5LuA0QhlwgteoF0cY8KNMqYhpdghRggN3mU66tiXl0hSHLbLsPSORRdJgi4esMT7jXzvFVXhllCqycx0EkrIbnz2HTndq9b6lAdDTxxLIEPcdObHnOAPLPWxlmrxzxxC6922DqkqlK9bmkKtrTHDuN1DrGP2p+Lf3mdrTGoskYwVJ2x89mR0QnnQtQM3jjXst/ujwZfRKtEOMNx1Cwq3uqOSsCTt434A==",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "sales",
                        "shipping"
                    ],
                    "cgCreationDate": "2017-07-11 10:47:32",
                    "stockManagement": 0,
                    "externalData": {
                        "fbaOrderImport": 0,
                        "hash": "3e474f1d119d973829736ed9f7e18a699ff607bf",
                        "originalEmailAddress": "",
                        "fulfillmentLatency": 2,
                        "mcfEnabled": 1,
                        "messagingSetUp": 0,
                        "includeFbaStock": 0,
                        "stockFromFbaLocationId": 2796,
                        "regionCode": null,
                        "marketplaceIds": "A13V1IB3VIYZZH,A1F83G8C2ARO7P,A1PA6795UKMFR9,A1RKKUPIHCS9HS,A1ZFFQZ3HTUKT9,A38D8NSA03LJTC,A62U237T8HV6N,AFQLKURYRPEL8,APJ6JRA9NG5V4,AZMDEXL2RVFNN"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1563850808,
                        "processed": 49,
                        "total": 45,
                        "lastCompletedDate": "2019-07-23 03:36:47"
                    }
                },
                "12354": {
                    "id": 12354,
                    "externalId": "47fwg8cpdt",
                    "application": "OrderHub",
                    "channel": "big-commerce",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "BigCommerce",
                    "credentials": "nYg4OGzKeMmDeVdlbKJRBg3WLTlxIWOK0or+WcNloytYBe91o1iu4DDfhTuCiat72+b+3G8yCA32SCXdB2+mzBxnW2mkSfUq73gHOKhQpNkBiA5dT+XYfHokcdHJErxWps+QtofTX0zFmINbvZOvmAvPpLdUblNMW6DI7IJ2wHoGFlo0UjB4b1PIZJ3HbgDZRD3RQWCIHbXgxpuOAKAJx6dHVtnx6hxqVGMrXB2CApU=",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": "2018-07-30 15:12:37",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2018-02-19 11:20:51",
                    "stockManagement": 0,
                    "externalData": {
                        "secureUrl": "https://store-47fwg8cpdt.mybigcommerce.com",
                        "weightUnits": "kg",
                        "dimensionUnits": "Centimeters"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1521127671,
                        "processed": 13,
                        "total": 13,
                        "lastCompletedDate": "2018-03-15 15:28:15"
                    }
                }
            },
            "accounts": {
                "844": {
                    "id": 844,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "ebay",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Calico Trading",
                    "credentials": "iCpnvOwePsMJq7J40bqlO77erZ5X+00dzKvuRk4PpSGCEsEYCixvrALXTh8lZ4anCsePIJMLRFc8MN0C2DNL7WWBffE20qfU4ZmfY6BtJjLVpXn3Y8/aLas6uI7BYX+xydtAavkSmiGJOLPEtQZqCpHT008zPFTA59ebB4tDe2DvZHIZAPoxMX+QfpaaujyBxpzw3RGmId4C6LzUJ2G5meV8tzw92/SMU5alnWCrX+p1LUK3tk7CJRFDU6PSOn8Lh8ZegQEAoMUGMEOCZuIvhopDmYiCm2PLvk1f+IofZXTufQtAjZBS5yyDTVqqKSS056zp02tyh3J0aATDFpVONkJ3IaTFRNpH0eG3nwwsI0RgaRPTNVr/c2Nhf/KblTE0P8iOus8UJZTIesgXQApt2yvUr/P/X/VD0gkXZO/nREmdRqAerC1Usx5mCLvAUBYoNo3el8jsdYFX2ykzbwFd0cHJGaQPujEdjmR4ELs/llTelUGT6v+MIrfw9cZQ8SrP2OziAP5lsrr9tqi9xG45dGas+/jCOWdU8eAxm5rcQEtDlWG1Kk74tbwWFLqMgrKIVE+yx5Xtud+cKgEp2IDD+4bc/7plEJBW0XQ6nMJPelfKq4DnQe4vw0hcgyJjAzJFyDQtN0xVlOmciVHRi44PTgEFKTVUmwBzwvxsNeUR1an5qeZ67gOxRHgndI0QVq3aKI8vm8+1arW1Hg7iYYbdoZ0L+Inl+SGRdQFVwfvgmLjV9YacJV4o/m2X/RUawj7i386r1HSitafwnICDgsOk/psvSb7phj4Z/2jxx+E5VjlW38v6bVpk6UYuGimbVyh9gqNGq3oX1rRPG7jAiUQTGIoSFt56BJFAEyDMXqNnzv3c/nYm+gTw40pmhPPAUMb30ZMecHdIG5ulqgaQaxADOM3Lc4VddBhFO9ejdIrACf+Az+TI4dzGgDnh/62yrS6hkdP5qR3N1LUQmyEgDH386oy7uQsoe57Dnuy29YNI9ijjC/3Zlf0k/O0SzqFCKGDOTOWPDA8yj5bw4ZnwyWE0Sl7FF3QshrhdmMlJ8hZz6oE8M3J8ynNPNzHl4k+ItplWSp+tnOgMv8r6CZ3/jvW1vfUQ1z2kzw7g8dt6NcQjFTbCAriDdhJPYTgeOtDRwaWpHuFrezA0suhYNVo/7CUyGzkOk1XFpMazNlBUKDFHFwGAHLMRLTKivg0r/8pQzoqROxUjDedGs8YXQNRAkQzdQx2cTEwW4yJNrEn9j8nFD+84l5j+xKTQfwkbfQ0AzVBO/psTYA4PAZDArtxqxiTroiMNdaZ3P8vXDpojkardR2QKsQEEoInXaGHpNzxLVdnrZcbRBCZMaWacecUH6H7vE41PAnslbm6E/0h1gCHK2tqYCLH1M/iYTL/hp64nPlPyCb3P0/TGu/gFcamxSRqPF4cP/MnENAtgIW9UxRsEEUbMVSvYxg9MtkADggF9pmL2L4Crkj+FbTZ7+yhRxhU2ycwbhZzoEXDOqPauxnDEXIbXlV0gJrUnhwIcA0NQi5JkyZukM3HjvWX4j/MB1mFsKlA0wdfVYmh8kIFr6bLCfjuipbC/sUIB/93U+rvSGiaVNqM52w6dJjIQZ+p9eDJzKyHy5JIipPRhCcMpBx5xnUA9rlwhOhy9wKzxRfUQApXOPu2MavivSO/8cP5mLdkylbH3T1vBBcuSVcHhQ+Wvhpd4R1zIAt8EtZyfSJgsiw3EsQHXfebAoKffXQNKX63T2bXJi4WAOrRYjPAsey+YmHk=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": "2017-07-20 12:16:35",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2016-01-27 12:16:36",
                    "stockManagement": 0,
                    "externalData": {
                        "importEbayEmails": 0,
                        "globalShippingProgram": 0,
                        "listingLocation": null,
                        "listingCurrency": null,
                        "paypalEmail": null,
                        "listingDuration": null,
                        "listingDispatchTime": null,
                        "listingPaymentMethods": [],
                        "oAuthExpiryDate": null
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": true,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    },
                    "listingsAuthActive": false,
                    "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=ODQ0",
                    "siteId": 3
                },
                "1096": {
                    "id": 1096,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "royal-mail",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Royal Mail PPI",
                    "credentials": "Royal Mail",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-05-25 09:55:08",
                    "stockManagement": 0,
                    "externalData": {
                        "PPINumber": "HQ12345"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1445": {
                    "id": 1445,
                    "externalId": "1445",
                    "application": "OrderHub",
                    "channel": "parcelforce-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Parcelforce",
                    "credentials": "2+p/GVQs1ndg/heKwGT6bePmrr9ElapPzIhmSYdggFDPypxFY+/sIyYl5nWNhBpugPdB/rWFnyon41Trir9I1tPLadwkG3tx4nXqeN1Fs417/NKHRZtZw2pYcLAOYiJO5egBD/wtYAVOWwTie99HiBsOXxjuOifLQ3/eoo2lgorjmnQeRJ5sKY535YOsHS3m0F47C2ypo5emUIw3pXCoSncxdDydOmrY0H5tJLUIA9nGZ7DDuNBQyfFuu97XsIExuriMw3qIg9MXPcAFy56silpxXdE8qMAlIN9NNJQqlcSOt++u6XpoeO6FEHXmvc/186H3Pi/XXwp/xpr7+0Y8FK6K0/rPga17hGWRLY+AidVnNyYl7qc1LljcEmhSXD58fpzMIOcH6XRjiV/giHHZ4EqTKBMIBpxwJ8fpqpJAGAlGs7t05vol/44LQ37cVzNp",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-08-30 14:27:11",
                    "stockManagement": 0,
                    "externalData": {
                        "config": "{\"emailNotification\":\"1\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"1\",\"sms_pre_delivery\":\"0\"}"
                    },
                    "displayChannel": "Parcelforce",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1447": {
                    "id": 1447,
                    "externalId": "1447",
                    "application": "OrderHub",
                    "channel": "dpd-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "DPD",
                    "credentials": "ljkIEIyzleoeSE6GrLzXJXh9nlRkVmiWu+tEm023Qsld2iu0461qm3LK9ZmwxZ110Jh/PBp8E1hUuUd61B/7cei8QWZcF8qjAq6IyZnkL+MygqJrScSdbowuiFSJfsw2oKiNH5pkLZ37HMyi/s4bNkCTOCzNIF+QBeWDX7GEXwXAkBhMGUIrQcXrjvf/aJV6+9D2Wv3TZqXRrZHg8HYqL7KJm1f9FGQ5H6Fxsn5Ams7+qTcTfV4nxKB7mM2aQxLbPF2rz0B5UU4kKQgLjc6p6ISTm+HRkEPqo+TQMZU9diBQOlrEm5MPBDK/y/QKZf8SqtEG7L3VKSw5pbpyThRUvcEeWsq5eW+r3zQ1bhzOewYHHD3psQWUlWlWC2+ERO430xrYDiihs5gOBhtG5rYI15g5Hz7GrRSPXTJl2KHeOrwTUnKVdmgOTYFBNwiXB9yHAMw79394xLhEpgeoZAon59z+n/kgCV+xf3164Up2DNB4ZXeC0bKCwZS5UU1aqGV8imcBrsh45MlaF/jDeRI+ZoWhOUjGdJZrqibPhAKnOG0PW4028tQ7WUwl1Q8qZ10AQRqQMTIChoiTVr/CYJ+P+fW0redHDDXzi2jSa4sp9sPnsmkCIP0wuOkZU3yxawpi",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-08-31 10:08:30",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "DPD",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1448": {
                    "id": 1448,
                    "externalId": "1448",
                    "application": "OrderHub",
                    "channel": "interlink-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Interlink",
                    "credentials": "NhqQH5Yvo2mTPMAitLN8XOZRGJjNKbu4ld0zvfLH195fntGbuiTG++69OzEB8uB0xUkNuWl26t4ou+Xl3A3pG8Nj4doKnuE7Tnztrn82wVrGMkJHElVGs06ffZdFvG0s2MKehldhazxC4ycuEbjDX/AQZkOTULoat+XeDeujvZnN2xxB+o7xpx5FOjeJTyyypjoFa8MEtGQovHpCPYO7ph/Av7MU15q9doddvrARtiIEK987xXMSnei8Q+eauGWUs+74javCusSj0u5QKPLOoph/iUNtsU6XPuMgSbUvaNpQBIR4HVo/ztnXlOx8JeNC+TrnBQX13N+5I267uYhMNpZyh3I4jj2IE44WeJvWCCBCw+68U3UR4DMccBKx5ClJkReekIwl9D9KNO+dK1lEjL62B9peb1JQ+RgFeulo1XB4otF/cJXD9MeliZVDF8P2rR3v7QhyHfpMPQdOu8w2/blnjpu1PUdUPQhUVACqPNJjYpLLSeSWZjZaTENJs/lTTmOSUAMYMsVwCNAWQL8zpgxNvK3PmtStI9g4uNhRPUUgt1d+L+Pu/wSqkVhqQ24YbahGfPHKCC09QODqBBkgaHk0IlcVIsKLOJ5efJBCP79HOpeN5ZvZpBhhni+yAhDxeRlk996cQJGl85xiVHGgU6Tf1KycBa+SWeKj+y90s1aKVU5yLhEJL+DNeq4vXHWMt5KvQoA2si8GMUoKzDnP1w==",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-08-31 14:28:29",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "Interlink",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1456": {
                    "id": 1456,
                    "externalId": "1456",
                    "application": "OrderHub",
                    "channel": "parcelforce-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Parcelforce",
                    "credentials": "X5d7L4B6DUXntVIsKEC6J7ULjviSYN9GsxICofFbFW6PswrPlEmAdeK7IU7ZjFrFRTPaP7W6e/Iz+jG+KqKQNCLVF+B2ggau5v22zwx4KGTl1+9TYhkfhGHXhA95m2l5sVBSNOiSr9ly/kprrBXa7l22ouWiXYXt2Fzcx2VbDjYd4zAIN1Tp5N80alyfkRzVM/RoQJ9IwFVoFHqMXE2FVPUz5VAriZ9LM5DTJHUYuS2bZ8d+s8c4BOzrwi7NZhEzzsbWtDF9gKNRKc/wqKW3idSMPGvSJTnjCGMH9+7FxHXhYN9BE/igqnluhIxUHttJ7A4FQw3yEypyDDybfJzta54pGULumsMmqkBSOZ69YgKCrYpgxfZdhfnzmy8hIiAwoTOZVsgQbBP4rcbFyyD/O+pXGuVh3IDeclenPbv3i0jMu0SsVFDwI5QcDoostNQMbhCe/+nuTvREI1p86aJyAA==",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-01 11:32:39",
                    "stockManagement": 0,
                    "externalData": {
                        "config": "{\"emailNotification\":\"1\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"1\",\"sms_pre_delivery\":\"0\"}"
                    },
                    "displayChannel": "Parcelforce",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1457": {
                    "id": 1457,
                    "externalId": "1457",
                    "application": "OrderHub",
                    "channel": "dpd-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "DPD",
                    "credentials": "fG7mb7o2273LKUcn60vpLqfgxMp0G7LArSWNJBhjacburSEe4dIIXIwv33UPngs02UrfSOtf6YxYLQ6+efXNa6NbF4r1WlcVlbsQq4kBrkPtHWnfJE/IUgj6FNC0p4vqMB/3bwaV6f/gJgkSeTMmTGnRtr36icakeFbgOG+n4mBJhMpH+CMErlhJnO3+7Kq7PoAaA/1EZyHSf5hMBnrU4ZBrFEaGChToDRaiZGPgAiFWs02BlzVXAFLQou3FD+UauH+zbW1kRXCd+OOYTG/ew4yPNPB8SC3CCHwci5QiESVIs+q/qCApLMBVPVq6/EA8bghNsO7VllIRhUqNaHC/X+K9IePaplS38FV7nNd8twLayj0Fv7JSNqD8BwgVWM+p5geadxX9T05fQ5ijqfCP3qablNY1hJWDQMnxbvhExxjSO0BPvaafYOHE/HiokdsCDjLiiBCa4q48O/tiLMgaR0kjpFmD8xcmZj5+fPKTCXKd6jssI9pTEtoon9dQhCo0S/kF174ke7r6vj/9lKr2rTdVGlhNoqhhxNet3AeXppMk7PZ2JxpiYFQIy3CTuCs6Cce4c3Gdn1Ws/iSZi/9PpMhP/hvUxYDO6SMN5AmI7S0=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-01 11:51:43",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "DPD",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1458": {
                    "id": 1458,
                    "externalId": "1458",
                    "application": "OrderHub",
                    "channel": "interlink-ca",
                    "organisationUnitId": 10949,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Interlink",
                    "credentials": "xt5MOjdt8njG8ACdxh7Bj0wTCGxMqU0wJXIoG8bM/JuUSSpCM7P+/P7OhAjROM1jnbeSBpT9UDmfgc23CaR2kW/ebcVqrRMwWsxDoC0yzR/adLgTn+TnV7JqGWYV2Te06IV9otvmWG30mOSvrawVTXM956dun/Al/hUAM2E8CJFFnG9nG11DKXfa7CB9X9PeCGGHq+YRuK/n7xI+s7WblT+BU1YSIyhGiSvzKCYIrNtNwDjq7m8RqDsCtYNGAUAufF2pACZKU5L/YF7ClH+5pzwAFalqepI6GjrnMkO5gIMHA1qpoiYBDlSdctRGIeteCz52n4vvlwHAhWQKX/URUiRm8JYUdwCKcRvKw7SuYm4DN4nEnjA8oVNOA0zvMMtapgvuHQDX10NJ3Zwahh8BLQo2XvjFfi8uHkJRYP4OqbCULWard/8jBosdZOPtXJFXF0ZGXuxQEm/vjNOfz2wOlhXAK8ppZsm3YV3xDv8cIglkWExxs9z20i0IBQYjON6xmJLymqwqBmWFo9AK8KPZ15pmOoWHOgAcfpKduqUbNoTNEfnLkDz+eYnpXvWRj4jy5myjKUoi/QaLBBBK0G+bH+61cAgsW8bwaI4Wl1+Hqc8OlWc7a+mrmPpSNj7291kr3zO8oM+C1SASiXjo0oqTfiGiC9jeMSsLRiTz70gLn83dqaLkwbtvFsI9z1JPVA27",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-01 12:01:39",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "Interlink",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1517": {
                    "id": 1517,
                    "externalId": "1517",
                    "application": "OrderHub",
                    "channel": "myhermes-ca",
                    "organisationUnitId": 10949,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "MyHermes",
                    "credentials": "h1S6HNiK87jLSSQ9mQdgnFR3kwFgymBbpYfKOKTOqA6W2GDiHqs4GRc8HFPlDPA4quy31sQY+v7aVieuGjrewV0mV7rExhiVhpnPE2e6YOr1OVk2FS3VVfVSKKhcMy4RVBppxlE1hPgW+Mwe8WHtW10AFemp62BcTQNXsSIzfMwNJVjYpm3yZklFUMWUiUMqJAsyi7QkZUKVOY/z36k0FVgYPMjeq+WdaUm4T8jvmVXJtLJheQjpiYD8C8vFutCMC5JwCEAOJp0EPUiniz+FhkOI7b3s1U0wUt8R/aI4VD+R4JnHhohsCJHfupcz9xMQbc+3FeociXqZJJ8JLHZSRHE1g9FcBCzHHAMaFT8z3Tc=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-19 14:43:13",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "MyHermes",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1518": {
                    "id": 1518,
                    "externalId": "1518",
                    "application": "OrderHub",
                    "channel": "myhermes-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "MyHermes",
                    "credentials": "KhhFj5g00yulMijSIH+AIhJDZOKhkCOvK8bmDmK5ZiL08EdLU1JvIkoZdb+/nLDwuv78t6Mkh7zjHnj2QyrDzVBYmYp1gIP2otSCu84PvwFEOgRIEGKXIp18kwYHMBkhE0HryaoBVwYnqORH5/vhVz2rmUl3q33+6F9oeKIEGziK5vqf8TjDXJklCGCahkQe+zjY1cPzQc43pLaTI8meQ6i5Fc2NtMglKrStfE3sysmOH8Qw0aNHzDs0R6egbZvvxbvcYDl3bqk6qpllOE6dqUTYu5OSkYXN5ckY2BzyuyjgpF5Qbt0ytCFp5WhngpdcsAzPBSJsbxLi45+KvUcnBCrtlxCbS+0kzxyj470rTR4=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-19 14:48:47",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "MyHermes",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1519": {
                    "id": 1519,
                    "externalId": "1519",
                    "application": "OrderHub",
                    "channel": "myhermes-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "MyHermes",
                    "credentials": "9ss6kvrwHKcLDKIZomeQeosQVceVYY83oaK2gEN9EWvxyZBgc36LXjeFXjzCqNdZgPJova1NC8lsOmxLEQ6rQkqJQ2ORcwP61RON3qtTVmOtbRodGWI8F6Tif5l4JVwN3V6U0eYv0HeJIAZS5P+y+qiEWNteN3oMI5s6O2Z5ROpFJm4Wmtr+mWhJstqHXxzfVowEr0jgzVV9ovv/I3ovHrm2oR18pJpQ8F9hmbKlWS8Mx/tsuprfKDXHB6yY0TtY1A9rVP/yVR6idBExH0WovwBSiWH/w55ZoqTdrNMvlnIIdQ0VBFezjqlg26DQQsvfnbWToT771KD0iskq+2HTY2vwjQndkoFFQXO0aX0Gr9E=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-19 14:49:22",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "MyHermes",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1520": {
                    "id": 1520,
                    "externalId": "1520",
                    "application": "OrderHub",
                    "channel": "myhermes-ca",
                    "organisationUnitId": 10949,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "MyHermes",
                    "credentials": "CktJet0qBgjEGaDOHPUDWB6KCpZAtwnby4eUv3mGBDbA/xa91TRzfWZp1W4R16wOwB/A5EXvYZMpxQZg4XtdRDnfapKH+QpL6zD3ppFnXlwU51dBEq/X3ulpR2VUSPoxzaaKsFW7BsopLwlNMkBg6XKPiy/VBawcdGocWFgZUEuptaBhywgJaX34BV5ozh3aECMrB7P7zHfG2awMDizXerCg2zjeiSr4oTiL1ohbMMMYoA+dr5JIWrCpk+KIUSymEkjgeHS1eOSSr/XqoaZ8RrB45XVYFzIlOXEsGydlGA3VTVhCNE6E6AsLmO0pWopCSx0aQDc7oUk04KDDFaGSg/i3aFMIxxL2s1RzO+ucQ0s=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-09-19 14:53:24",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "MyHermes",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1822": {
                    "id": 1822,
                    "externalId": "1822",
                    "application": "OrderHub",
                    "channel": "parcelforce-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Parcelforce",
                    "credentials": "S4gu60+S+paiPJMW7SRwvl1pT6QI2trrbjLFXSU0W52/RtzGLT10+TbGnh65r1EL8/imaGzb67YQgbU/zDCE3v763VAP7gKfrq6ifPlHzluRaXQteGpmKzQQWPp39q6XgTzzAANLI2otAzTrQjZXYY9fUCaYdGyR2QmxOdhfZlbJQBq7cmvOHk08fPy+3DYc9sIGnOXLLpdS1rJ2apJWY03oS6d9DLwXRvfKPrwHW8mofDFl+WK4gZyRUcrlLTp2v2HrzDw9TPkqs7chL+COpbscgph4soytOYCrl/Tq2gAqjVjoC4xaUCzrbZ1RY8U/GpxFcwvJW0Gi6ZgU+4UEYLFeGa5He61pExi+bmwp2Wbase4DJjfipO4anqqwySM4iC/xjKJMg7mD8CLWoLHqzsYw4ZvrM7tg4pQo8tBVDlidp7S+DVg6nDMgogppJ4XbOLW+/62n18TvD1DJNSGLDQ==",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-11-16 15:41:22",
                    "stockManagement": 0,
                    "externalData": {
                        "config": "{\"emailNotification\":\"0\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"0\",\"sms_pre_delivery\":\"0\"}"
                    },
                    "displayChannel": "Parcelforce",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "1823": {
                    "id": 1823,
                    "externalId": "2015291000",
                    "application": "OrderHub",
                    "channel": "royal-mail-nd",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "CG OBA",
                    "credentials": "hCrj92WJJBP/ZwLM2YAsOUQpIJgNNHR1miGevkZ+ojdpUQaP02BEdQnku/fS+aZPgK+iCw4eoJuAD+2qY+FuIL3BC2jlfZCvlJ77R+bke8Mpp3/iUSFCenaWyxSbCpId4AsgilA75jx6vp9iJb/JfJ4E4ptVX+xXKkbasftz6zahmX9ZPGyQ5xF5EuPoT5jIPi+1Nrn7NwczmdUgFXYELPjeVCV3Mu4+Fktfd5u15sL0IN8N221wSo/iXsdEb/JkXxwiyyNaUJpplsdrRTF0tMRaHj5iz8NbxjzL/q7DZh5E8zHHGcCbWoQ5ZdRaUWEA8W6qF3Snxk/Q7KgHdmFYPMl82/MFAuqVgJN8JDVKSGpoxdB6Hew4iC1cibJOJYJbyTS/j8VBiAOex5jLRjwGpiX8cK+7tsWRdhcP27uX8SgZqvvBImyH9kp901/V5HYFiDGVJtd8j8zNpTVngEJ9szBrFVKQSrOvob4ZBCOLASpNP47CrYYmcYXcuO0hy1jGvSL7FFXZaYGUXFcZsZ84SxpgZR5GjwPqF0MctEs85xw=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2016-11-16 15:45:10",
                    "stockManagement": 0,
                    "externalData": {
                        "accountType": "both",
                        "formSubmissionDate": "2016-11-16 15:45:11",
                        "domesticServices": "",
                        "internationalServices": ""
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "3086": {
                    "id": 3086,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "ekm",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "EKM",
                    "credentials": "gwdBr3TbEXYietikBI+mOpc/BS8iOF8h5kCjQkIjHJQteqPeVH7Kpb2PHH8gA4bUD+nu2YnmfNKW8BNqRfvrwxX0jGIXBEBpk/BXbyvuu0KuYyoguq6K3iTbaM7awC2acUBeK5SpaRSnGYB3zODVtFY/6neMK9b5fQOhyWm2itMphSkEicN/9g6z8/Q3myo/eT7Wj1yf2SaeyA1zrp+MwzrbiVt5/800uYARkIvqqu1dYQdKpKcuHH3a5GA6MLupbPB/CPHldaWGnv2kIdNWiWz/6SVSJYI7jmru2Qnvt/mdHmFHjXXOvNl0b/bZoQaEYm9xwCPC6+14hS4bsFnFqBqoaDnl8/1PmPXoOofQ9WQI6Tuhncu0xVJIONdIN6zhIpCtKK0KylBQ5OBnusHFDUhy3F5WFX+n3K6+WbVWbNWOCqmDOdePsCWM9pTBFvpPJkmHeDeuKfu21by9Gpc1KnnwKdUcmWX+X+8kO2m0mgs9xOrlJ7+WC61TQe93w5/QIIPRmC+CjVhOawZwg16M9U89k26aQMoEWAr5PA/MLIlElw/mVdlNwVYig18fh9hPBlJMcpHh5YTFUosyj1pP7fcDImmodxyoH5GbF2elwB11sfNyALsNFvz7mDBJt9Bec3piOaS3mCGGkwbbuUMSWhbpX6Gd/7hC/ZCp0lvon2AXt/pNAPKZYsPURnShF/D/SbVMF9qDFO0Fd6o8dwsUAPFPIBvDl8MfBrqs5VKa07BqorFZ49QiOuR5jTKDAWpY",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": "2019-05-31 22:02:04",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2017-06-26 15:12:05",
                    "stockManagement": 0,
                    "externalData": {
                        "ekmUsername": "channelgrabber"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1560910068,
                        "processed": 392,
                        "total": 392,
                        "lastCompletedDate": "2019-06-19 02:13:15"
                    }
                },
                "3169": {
                    "id": 3169,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "ebay",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "chosen12010",
                    "credentials": "UA2QeZxpw9ep8cbz5gSaMPOI2S682+fB8XnO7q4128yBxcp2d6tlGt2M63SuwTLOnC+IZv8K4nWetiE4o5APCqm997qiPh1GnrNxcwA5b5uhhDOz2jFcSuJ/F6Yl4QjDQLgPTGwTuoVnXob88cO2d1SLEpHkGjXEtR2XOMi9RNW85FyfQpUtzdO9GmSaExjNiGhPGYeI584qaMJxCbAQ+CPLq8i+9Hyg1L7lwdPONSVsDGruBrDX3JeAXdSEV+MD34HWzyAWqqU2NePseh/zr9ZVLBzXj4tKq7su0yrpbq6knM8mCzqZKom3zxeC6rw7R5RCHDrmP1HkzvS0PpGjWz3kx6/9GJEVIMMWJ3JqglQDiWoBalXT41lbtxFOuI3140msja1qdmavPesf5ZAwHq2ryCCtl7DlRLYb5m2EJyH/mPYS0XP+f2n+DgDNXtQOtclPyR/olO68VClw+AoQHSDnqyh3Zc4WjmaLhpsDUz/5PBwp+dm1NQhTlzZiEXk2RDCScILu2ZPddMOclnrn2a4QXwQGag8AMlq7p4sBMU9MLvO073YdrzcVvxNaXnxAoaIZ1WD+W6OdA4SNjdmlNcB2gR4pNjm88paG6kEd+SGGgTTVddA/fee5kS0OtI1S/ydgGqjLsPYIJE+kTcEFc1uAOkyRVMNTN3T2zwjeleRsLTZ4PkXABDokhmO/r0mo6dxjThId+xA7Sq6Jg7MP+Tu6WveS/UbjQrtC8NicbTgxPzu5Xa9rCcK+/HyC4zwdFaCvC3MQSaGkn9MvsRcVKQklQlOISZaILV10B3/4YM5JsDmDIZr8V2hYefC7JuvVXctaiGDRbsw7Ju58vTi65dNXA0myisoNDR0cai/EvNwHszYzaocCdX5af7NXaeCxX+yitu/J5EPMmDDEiFAND9Tsf1wf/bF83bpalEpKsAaSnbvn5RE+6M8xC+oiW+At8zBLEK8SZb4fzfI5sj96Eu1qmpHTYnAUxvCzcHZmnZAlfmji6t0EfxSe0NFulzHKPPcuoUzgFFofrBhDaDXBteqMFquufFm7+vAp4XsVKgA0yAVdfi2U6niUhhw3G4D5zDbqyoKAkNDS1gnVK42sAAInZQGimQo3xO+I3nNECsbg6eFSWXwHkfgAnAq+neVZjcYm6o+qWv8To/MSw6hWE8yJc94JDu4nGKUFLlv0xE4D31sozgIYDhLggqwTAYyVIzNguxEDBo2t3cCcI1UF/+dgKXrZ3wiV4YWZJJxz9MKcLFm80P/2RITcQN9W3eVDN9wX0XPJsMYNyJp3BTbCdifrOk4hoF87oI+IeM6369uLIq8LN0x7ZHM2+GrZfXh8hSBiQnV9H19JI32/45QXaR0TU1BGVWXENy++mudI8F1ear3PDtUvxIg1kM+qHNaGah6braiv6XkYRLlH6b9YYk0BPQjoCwxyQ4HIzs5XLhr4lRoXA1rxuNeMRwV5tT6gBhsELw4vdVzKDDwhBSPb0ei/cbqc3oj9iYppLI8pwGoCQn/vBqDJaRJHoJcL9ZwE4CXFqgegU3arIGXqSFvxqlXQX6Uu+da371pu5MftsABT9j1yJSrXvfGxQrISF5SIz8gzgSHa0o5rZweOaqeq613Gwvsi8lpdMwCUX/kTGWArxeVZjySm/g28fvUgztpZGkAKcCVnSH2bzvLcvaqP6X3ezUAdfG6y+Lv/dHz3ZUFBTRGU+UN3JYDcL88vCkKrrOrsiXSVlgpo8+chOtnKjxnJFYnIWvLj4+qCBcD1NPw=",
                    "active": false,
                    "pending": false,
                    "deleted": 1,
                    "expiryDate": "2018-12-27 09:19:37",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2017-07-05 09:19:37",
                    "stockManagement": 0,
                    "externalData": {
                        "importEbayEmails": 1,
                        "globalShippingProgram": 0,
                        "listingLocation": null,
                        "listingCurrency": null,
                        "paypalEmail": null,
                        "listingDuration": null,
                        "listingDispatchTime": null,
                        "listingPaymentMethods": [],
                        "oAuthExpiryDate": null
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    },
                    "listingsAuthActive": false,
                    "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=MzE2OQ==",
                    "siteId": 3
                },
                "3170": {
                    "id": 3170,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "ebay",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "eBay",
                    "credentials": "YnFTJPtcN2vdvLi3beaLxVmry6ZRq1g7FaltD6+W0fYrDGon1EY6sPOLqIjyUz7LfDd4GSfFV5X3svlWdNQyFNqWO/0nvF+Hte/I+wfv0C/mKOYW3yQ2cfdiTqAcsTkAFZC7gmnS0f42KDKntDyqNqLYvfoMH60r1f5z7GFTlIeeJw0ewRw+Uw5TmveiAcb9Q6NPSycQNxnK7zeAOUvZZ4bsIVWdEwS6wX7K7oP/zjMlSdPau7+E6BjrqkuyfNIDS8Tn9xKvwmigNn2yu1tp8WbrvXXonuAlBYZcmVX1nXwdvw9sMyRQbV1Zm8HB/tR9DloBjadUybCl/dlWFWovHd6xA0d3vgXLDOVF5LpBPLFlOmaAKp24f4Aw35vR8qMW03A6+s8jJvdSBkepwrvlNTiK3RfAx7np3Z9aSBA6P2BVpUrXuUvVwFI30Ub7jLBmIjIIyTsHOIpiM+XPBrQv3g6sdm2+oPh/5k1F+M/6ZAM5Pyml+lgnqEiGdF54EXT1cZiosVxcThC8Z5cg2XmbdU2ZHqQwhArbzZ59ne1moullk19yGywWK3JVeGhy87CHqyyJGZeo1MB/DAikuW0t1Aozage5nhGfkiBzcsrRT29PVyFMGKMWCqNLJQ56dXkllbwd6HuKsxZwGTwnaqJJRWE8bRpaytOjAs9TyUA8Ojo7/+Y4T7ozK3kbP7RMrPeLFFM4rBCCJrsjaDHM3IQOlAQr9AbpOwrb7faBtC22xdXLW3l+WDo+EpulNQ2gNdiyMO9pBinfARuW9UblAJRosEUsw/tgFM9rz55YwVFQpPaMFfJe2EVVWiXsbNlIowvvDARBu2CDm9Ti9my18LHYkLq66NnKiqSwiK1r9fT8jw3nb+UtdTvLXgRIeYCkGkxLcUUGiIdtcbPLdC6U89kmNjcnoTyl9gJQ1q1WzzVGI8FWIy/YLJBGTTRy6728mFnlWrPE6JyCDAidb4V4RE5BQNLFJIY/bICprRoLNUHrIbhjiujhcU/P12NpxtY00r+FdAJmxMO1LnPl2QnNsG7pfEu093Mof25j/NkT973TwdrO/yOsd8sR9KxhzmzKHGl5l3Z2QDyMO2Kc4/mxwUfm6J1Ns/Z3K9eWjLDntck3302oC1Hcm7sVTx8xJ35sTX8VzvBBdspWsavyDs1fCfvwSKhHK4R1zpTTh4a2ZEJV+M6BroxsELe93/3mwTKHhVKK3U+xsLx7LevcRdDpIo6rcP2wylQeyRXsw+d+tw6bb7RTHL3D7Mt5l3dWDpV3KyYGzEQZao+2lm224GvtZd15Ey1FCOBVi/ks0+VYK5bOaz/bPLNDVqCW1deOujg6V0kyHm6Iy0LROngS38G2ZooN8gEaXbUdE2muq7ORC4yXgs4diVPuQo63OubPHMLctFNU/LdgSjXGFyGbzc/TBMy8nxMsijPtVbBCt3A9oUFeIubRLlP3OkXmjPrnNiLQT3NUABFOrDiIH+6DA3fNu3+8o1JDqWcOKvQhRwsRXskuJ5WSpEW39vg8M8dO/F3V6uYe2ET9t2bQhis1CuZmEFo4EMghZZE4+6pco3v+wCTi4plbk/Hf0f9MNUHA8MiGMkCW+ZsNZX5mV24UGbGHcPG+D5LyNPIeUYJTto+yomDArATe+xY1m/PNPgftBqEngESjbv06xXwL4NI+74nch7KDFuWyLLGmZxsisd6u52jybidgkjJzUFrq4fPRvDt8P+XLBjloFlBovlaOjeQGEqJ+2nrBvGBFyvYGrUxiOmXab/6B1o514kFuqz3hQeu4UZjy8Wv/Or9vv2KobqNG/SQ+9Q==",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": "2019-06-10 13:30:29",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2017-07-05 09:22:16",
                    "stockManagement": 0,
                    "externalData": {
                        "importEbayEmails": 1,
                        "globalShippingProgram": 0,
                        "listingLocation": "Manchester",
                        "listingCurrency": null,
                        "paypalEmail": "accounts@channelgrabber.com",
                        "listingDuration": "GTC",
                        "listingDispatchTime": 1,
                        "listingPaymentMethods": [
                            "PayPal"
                        ],
                        "oAuthExpiryDate": "2020-08-16 01:02:32"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1560132005,
                        "processed": 0,
                        "total": 0,
                        "lastCompletedDate": "2019-06-05 02:00:15"
                    },
                    "listingsAuthActive": false,
                    "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=MzE3MA==",
                    "siteId": 3
                },
                "3243": {
                    "id": 3243,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "amazon",
                    "organisationUnitId": 10949,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Amazon EU",
                    "credentials": "7KcoJhcMl9cT5rTLVAL58CRe2hSr87TkfK9zdFb2urppxk7EPnDoisnizeXmEKMFeYlaBwVoYJG/J8rIB+MwSKJkisYeQ3Az/egy0uwOs3UE7Z/UunXuA5xMRIZLI0GkzHYH0DW/67U6/zrSNdNuJ+QXgQLiKIVp93xExHup3yGD0YLHvxNSw58/RoWrRLpL+TXpT6X9VHfigokGHrIu4uwRnyByUyQHaAyeUGBxItx/pbeldHE1T3LJoY8JiGkoQwIUYCiE4jqoLHCwXqzZ9pWQG5LFNiSaGkA7WmOmTsWZbymL7Mg/YA7DzyjuFXS1mfdDFCRN512M1fshjF+X4psCnutqb+k8UgBvEHGedBJtQrfecrFRmkovlWD0Po62JCzpJAGCKIQ6IZqioKZQLEhD4J5AMcmx+CiVHku52/5WvByALEWvk20Mu4nthBHMnrc8whDd1HNzL+Idj6r6PaebVX22E1mb8w7pUUcu20lA9LNwcBoT4VDHHXtO5nPwD4vsPAUykf5es2vuEHZfhXDN4kdJ2s0ZS85dqxK53Qc8IsI19Bifb0Z4ofOFhFApFJ+8Bn6/Dcm8KahJzH76F2cj2bSevXflDLtlbSzQjILyYP9UifJncZLriu6mHKv+b4oshBCzIl03MdWXgoLUrHhgOiGB1jctzTp92TYX0iQwomQDHudS56ZaGGDBMlJtED3F6ojBhNBZQy+vIeBTcbIBgs60H7PgAjbPACtt/nN4zlw/eOFQfUEyWW3g9lqGPaIGwZJFUZeFC1F+DmfyReyoOqOaHhe9HRYWxFVx/QDjTZReHTHtSFIsAVX7HH8lyH8XnJqVKqdIztJWmikcoyj4Djj/LtcOFlktZFRHtZZFZW9i8/gKWj5LuA0QhlwgteoF0cY8KNMqYhpdghRggN3mU66tiXl0hSHLbLsPSORRdJgi4esMT7jXzvFVXhllCqycx0EkrIbnz2HTndq9b6lAdDTxxLIEPcdObHnOAPLPWxlmrxzxxC6922DqkqlK9bmkKtrTHDuN1DrGP2p+Lf3mdrTGoskYwVJ2x89mR0QnnQtQM3jjXst/ujwZfRKtEOMNx1Cwq3uqOSsCTt434A==",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "sales",
                        "shipping"
                    ],
                    "cgCreationDate": "2017-07-11 10:47:32",
                    "stockManagement": 0,
                    "externalData": {
                        "fbaOrderImport": 0,
                        "hash": "3e474f1d119d973829736ed9f7e18a699ff607bf",
                        "originalEmailAddress": "",
                        "fulfillmentLatency": 2,
                        "mcfEnabled": 1,
                        "messagingSetUp": 0,
                        "includeFbaStock": 0,
                        "stockFromFbaLocationId": 2796,
                        "regionCode": null,
                        "marketplaceIds": "A13V1IB3VIYZZH,A1F83G8C2ARO7P,A1PA6795UKMFR9,A1RKKUPIHCS9HS,A1ZFFQZ3HTUKT9,A38D8NSA03LJTC,A62U237T8HV6N,AFQLKURYRPEL8,APJ6JRA9NG5V4,AZMDEXL2RVFNN"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1563850808,
                        "processed": 49,
                        "total": 45,
                        "lastCompletedDate": "2019-07-23 03:36:47"
                    }
                },
                "3250": {
                    "id": 3250,
                    "externalId": "2015291000",
                    "application": "OrderHub",
                    "channel": "royal-mail-nd",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Royal Mail - NetDespatch",
                    "credentials": "TPAJ661Zv8weflgLAfGnxOUuidrIqYz7h2VF+DXL62F97ORyRp43wkY66Xf6AidA8MPWZNmo3QiBHLP5T7/mOZ7v69O1wf07NGm/1G9TvAH8RmJXxSmk069W9MTANKOgEkaKQYpSEyRG23qfYHx5bHgg9gM9+ljgEbbfpwVJSIMo0Ve18dFSGz28O5y74k7tcmbHyFe5NWjR2imIEkvQ75Ph4Dw6Xj2yY3d5W6sc4EjiRAJ7PH/01xkQFGuVkbFIARVjk8qeMnY9qOjuqrWoRUrJEpldvcuTj6VSwjtsImEDc7S8vcBuqtuLHQACUqi0em4OSOsEUa6Uty7rodNGhArJLkhmHX5KMX+tjc+tqunrHgTk1PW4OP0gqJx3PqFKnS/DWlQ1DzNe4/OhBzwQF2+zi1ovdVJCtj5Bt+L1fpYGad82rwHa2j6mTTnQXPNGaa3uBpXKDALBz2/s/XZNIXElriH1/h+UCMupDfDiCiDJ2SeHU8J9HnraiYswLVmlUqL468lqoL/9ALeqXeLzIW0LM5lFz6df8cpZtTCV0Ew=",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2017-07-11 14:38:55",
                    "stockManagement": 0,
                    "externalData": {
                        "accountType": "both",
                        "formSubmissionDate": "2017-07-11 14:38:57",
                        "domesticServices": "",
                        "internationalServices": ""
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "3252": {
                    "id": 3252,
                    "externalId": "3252",
                    "application": "OrderHub",
                    "channel": "myhermes-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "MyHermes",
                    "credentials": "7f0kv+R/TAgt0FTTQa/jAgjjqKeSJJEqscqyXH7V3jN1DE4phocq1MLfFFYGJ0a7AqSagcRmtARNBXvSRlw3nzboLdHgfTKdaOybiEmDmID2zI1cmpNdhi3h1wPelhCWOAkoPGPSCXyndPc0AzVDWHzRte2v76B5WJM7+QuVKgxxELxEMjub5BlN/WbQhjho/rCSTfQPW5Dahflawhb8eRPGKgFq0IdymRAikIXylt0ofznpXkIgxdiqvg9duxViHxmPQ41643IDrsosKt41Bm66fYg4e2WlU00l9ryf7upXbOlhKTFpvHDEDBy/GpDIMp2uHWf10fz1QzOjxdK5YIGwJF2mO9br6rFoN/Y4AVc=",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2017-07-11 14:43:26",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "MyHermes",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "3336": {
                    "id": 3336,
                    "externalId": "3336",
                    "application": "OrderHub",
                    "channel": "dpd-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "DPD",
                    "credentials": "gQR0bS/8kMl1ZdPqxuByLRgIzhL8ifWeqzS9R90xxZP/0lQR5ZhTPJHxLRD7FfS8PLepaPkA34Oix82txMxXWBzj2oWxO58d6+NV8s7QjYwo2Nt4TcljqCBUa2q9ci2bXAAEaFr1Rmnn/Q/DfLyS0NWbPbDjknBP0+//MK0BHZaHU4sQlMTe621Bor8up9S4jQZpUJpc7uksyCJwxG8LlhzNLlOIB7bov+KWx4zfUtKs93uGnGlfoUXylNVFCCwqJBJkyB++HMZIY9HPslnQ4doB8U8zwTV0zcu3hUdCWahbeEPSR8/zIOQn9GyOftzEWqa/3qB6VwLkg1DbtVU8DyCIbhcrzLaz9sOkl9XnMid+ZT5Gp3+w0auL7svxqiKSjmDvG81uifFbJxZL23Xk8EBpl9Sfy2/kTwpVlOlB4sy0Mm2zN1HeiJXb54tPBt7plNPYFWtxF83Ij53uL+cDpPzot8KqlK4DPQ92Yr6xRqoAqY1Vsw5okylz2k48Rw8q7sipDQYuza+A5v8LMjGVQzpl/gl8rh/WtcNxvyL+D4vSWafxx7GfIglUfschK9EZgxp1pC26UWBt7B41zQQIaSqKRmiCKkN8ZxrbQ5TyHUEY8LlZwJBwGxEye2qW6mL2",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2017-07-21 14:20:27",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "DPD",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "3337": {
                    "id": 3337,
                    "externalId": "3337",
                    "application": "OrderHub",
                    "channel": "interlink-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "DPD Local",
                    "credentials": "VsgnLNrzpX0lbxDf762CWR/zHY4J57fFZIPPMuHBWSs7q1woeJfGIhJNV3Q8aJufgNlA6g9ipRofQjMMT1GcGEcus5TQYzLrMag3PekIzx/LeW9HxYeJlS6+mVJ6pNki09+ePPLJfUhkZ0DgTghmh0Vk2/0Qst1ur9IvRmdjNoqDtCF9avYJlq3q2nxxf8erS1x6QwMe6h5BM4vOwJc44bt92/ioK6q+KaUJYrbEKDTv+X9skJuACyDVkrdC2o3KquD3ie2hKWfQ1BeXhlfId1WmO0KXFKZpn0bVAIsYPadjlubUmIxBw0IZ2vLMkkVQeJnmIIdmQ1ZDpD7YvZWNN/wYzHavIBa6+0UEeCfr1AlSw0bhg/F/ZWn4j77njvalqMbDQjFaiTALO2NofS3htvF4RQZVwEYXEKwtYZyaiRKotIY1a2tYAWOQmVJOluTPZfwD3WTlvB26xfE6ENiK7sgLiE6/dbrN4xTjAsTAB6d2Z0Hxm1Oa0xaUIy0/Pyg9oUUCkwzAlyP/dpXbtMUQiLbJxJg9uVMLw44lBf8WXVz5KcJK1/Hw8ZDfPWMz+dIj0uInuaA0kVyxz/xx8IUKSf21a03DibXKRGKDM0WSlhAO0+1gSqPQPLkw+srgAg0sRmzIXBk5rpq8opXd1QLv4ugYvkGs7rh+OU7mcl0rNIHIT+m8Hpn3SDeW66PYhnxqtmX2PAJ7K+jDQaKxmCguEA==",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2017-07-21 14:42:40",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "DPD Local",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "3747": {
                    "id": 3747,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "royal-mail",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Royal Mail (PPI)",
                    "credentials": "Royal Mail",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2017-08-24 15:38:05",
                    "stockManagement": 0,
                    "externalData": {
                        "PPINumber": ""
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "11660": {
                    "id": 11660,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "shopify",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Shopify",
                    "credentials": "2nfg+u+z7qgiHaUqL7yEp5wBIVm2mDW9bW8IRa/tbJ/NVwBsRfGfZD3QNc4CHhkjidsA6bUMGlGTIcVhdvsB+yEecd65eRhg82xhJ6Phmwg51zsVENmCtRvuQ2tjJGpibW3M8gGAW4IJ+5eAdJbvG9jT9+OqlLLGVK4FSZ9+iQoHjKsQ6DqoQd892BOl7dFkcKLmSbKEAXQQXkRU0D9sMbecSmACoa0CSfBCGTEqgOE=",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": "2019-06-26 12:25:37",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2018-01-04 16:40:43",
                    "stockManagement": 0,
                    "externalData": {
                        "shopHost": "dev-shopify-orderhub-io.myshopify.com"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1529671637,
                        "processed": 1,
                        "total": 1,
                        "lastCompletedDate": "2018-06-23 12:47:32"
                    }
                },
                "12354": {
                    "id": 12354,
                    "externalId": "47fwg8cpdt",
                    "application": "OrderHub",
                    "channel": "big-commerce",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "BigCommerce",
                    "credentials": "nYg4OGzKeMmDeVdlbKJRBg3WLTlxIWOK0or+WcNloytYBe91o1iu4DDfhTuCiat72+b+3G8yCA32SCXdB2+mzBxnW2mkSfUq73gHOKhQpNkBiA5dT+XYfHokcdHJErxWps+QtofTX0zFmINbvZOvmAvPpLdUblNMW6DI7IJ2wHoGFlo0UjB4b1PIZJ3HbgDZRD3RQWCIHbXgxpuOAKAJx6dHVtnx6hxqVGMrXB2CApU=",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": "2018-07-30 15:12:37",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2018-02-19 11:20:51",
                    "stockManagement": 0,
                    "externalData": {
                        "secureUrl": "https://store-47fwg8cpdt.mybigcommerce.com",
                        "weightUnits": "kg",
                        "dimensionUnits": "Centimeters"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1521127671,
                        "processed": 13,
                        "total": 13,
                        "lastCompletedDate": "2018-03-15 15:28:15"
                    }
                },
                "12355": {
                    "id": 12355,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "ekm",
                    "organisationUnitId": 10949,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Acme",
                    "credentials": "Xu6vYvSef2w9DAA3fiZvhLYknLkLPyN9lGehw++yU23TAPfkgFESdOvWztrilx4/twu7etv1q/3xrxpQ2ZqZ6lE9MqsxovvQCRYZRTD2anY08cuzlDNJ5Xm7cds/SPcuA0bLkbkeO2SFgVqrc7cF4fJYfO/FLQOY878LYaTvFJL9xT8jx93gzf8TGDctB1IABpkLG3kaZ/7t1gD5adBukAbhzu9CA46r0YyqU4rDqFTGDS2BVp1z/p31ZFTElA42nRsHJdoJ+Q/ICfjLfD+NlELsRWne1dp4Y0x7FzZ6djfcS/ZvtWoPexv7Xz0VcGdz4Bz9odLqI50TOFJ+GPVOeE/XX9k8Hk9Yx2P/j1R082HZpK/NZlKdCD8ovh/g0oO4dSaZNYpKoZs3uUWogN56GvpQlxUf1CtorODaTTwBPPbSpSAEzzbtMfzJJSdbot6lM/hOyurtuwEAmao+V4jZwX7Pqq5DqrTXVZcHTtSUZQ2BXOP6W0ug07rcnXAXAd9dNcmld5d6ywHFhO1U3osZYrbat5niVRORBdmAu3842KhfuHefTnZ9D34H2YpqcR3wVsEd44oPgSY3EJU9n/lvUj/Aohn0Oz+uoOXlXPCiNBl1wQQ61CZSRsQqCF4tWgDdQsUb0wEJJaUSJ/JcnFBiZyEeTk5TnghbxhXSpSvuDcBNgh2cRUhal0mf/8+zRY8rEymTDzRxXoUd9InS3YJS64dfwJvJ4OOmq0bI1BXlMXs3IX/XAzS/Cyj8oAcw3eIx",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": "2019-05-31 22:01:11",
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2018-02-19 11:37:54",
                    "stockManagement": 0,
                    "externalData": {
                        "ekmUsername": "channelgrabber"
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1560910676,
                        "processed": 392,
                        "total": 392,
                        "lastCompletedDate": "2019-06-19 04:34:37"
                    }
                },
                "12628": {
                    "id": 12628,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "woo-commerce",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "WooCommerce",
                    "credentials": "n74zEWIEcNi2aN1IiseeY4EBTnZxbT+pIt/V6XmdNHJSxK/0SPaiiSWKFbV0qSJtk88YwMaTf2wIgN+32hsCPIertpodyhhccXY1D5C72z07zEe5R48fxaRtPOcZDdbcwQzKbdz6qMta9o5ZyAlsVqYmezGrQ+tugX0sx4wACg1caskGqzGSjsrXMsSzTeG5/S7B1kT9qhXXE7vGBks03Q3l1RK2lbdd1ilO1WorAQZVtZuCugBuPuINcADQ7RhkqloG7UALR5QuF3oTdofh5ZrqKwx8c0FCQErZKn5El9iWO7NvgaHybiizYrIPDUoAacRxpJXx8Z4BjlSrItmwlIMC1XPr/jzOh9CVU/9i0Vo9BkoOpHGXP0ykzP2fHdw1hRaV3UbnEe7QnR5Oqf1t5wdfCNOuVEG3cNTJqSY87l+6XBtN0918lq1vT8p2A5n56GuKMRsuzrb5afgNAfDmXA==",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "sales"
                    ],
                    "cgCreationDate": "2018-03-09 14:20:55",
                    "stockManagement": 0,
                    "externalData": {
                        "decimalSeparator": ".",
                        "thousandSeparator": ",",
                        "dimensionUnit": "cm",
                        "weightUnit": "kg",
                        "taxIncluded": 0,
                        "currency": "GBP",
                        "sslEnabled": 0,
                        "sslUsed": 0
                    },
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": 1555459231,
                        "processed": 92,
                        "total": 92,
                        "lastCompletedDate": "2019-04-17 00:02:27"
                    }
                },
                "12917": {
                    "id": 12917,
                    "externalId": null,
                    "application": "OrderHub",
                    "channel": "royal-mail-click-drop",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Royal Mail Click & Drop",
                    "credentials": "FyGQTsTo6FxxEgfZP5u/mg3S1GYllTb1Fy7Rs6Va50hJKMOPelFoKHpygFXmXHysCbIR9GpAjIdCpopxwHuwvFAe5o1azYz+WlSKG8VGPJuDDFPhZ2dFPlW2s8DScHpjFO2TnH2D+7DmauR1W/Ttm2v8FIoWY2Go7+S+GE3fq1wZmf3eESt84Dn8hsx39lzz",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2018-04-03 13:25:10",
                    "stockManagement": 0,
                    "externalData": [],
                    "displayChannel": null,
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "14098": {
                    "id": 14098,
                    "externalId": "14098",
                    "application": "OrderHub",
                    "channel": "dpd-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "DPD",
                    "credentials": "uUXAEpiPi895LUMm4TvfhhQDFNZUt6mMfjkRyqEKtBODeuPnx2f5EmQUhp8AQmYbMbyM6B7amdpHW0alXAG7gt/ufw1Ndhb+iK/q8if8V6pNK49HGba7f1jY5/0d9+QEN0TAeadR0eSZeawW1BweyvpGx1b6sLF21ejgecRKHPtFPniv/Ym6EO26D9OSyyZSIZygyintBBX7r9fnCiCA2BRk/IR49CrdYTz5jeEubd8ARsY7MJXZjE6O6/TePqYKzXio4Q+GnA2i4Tc+dt9WbfaiRHQjpT5daot6wDEWeIDtm2fe4w+f44uuitY9S5zSVdcMFO1Piu7LPk6ohoebtoK4OQUZpQVJDMNlO8vgX4zbgT1GxYzDx1FdVtniKnp6eZbt3rp+2h3WNbN27w3NoMWQa9Lo9SHaz6zHgnhTveTFQ/oW142T1n3wEzE4qKAurT2hOix61b2uUO0wP9R95b2v0ryLxLRjmI2KX2pp08UBa9VAgAh22fI1KhK38LVigCE+0doPUcEnF0FeogihMdtC9ZFRP13az2+sfw/p+STwbCpujEdfg4qpDLRAkE0saEUVBBpzSA4ipunEm++PrsqDv2jXlMGeP+ViUD+m/BcW5von+M/d3q9JsjT7hzak",
                    "active": false,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2018-08-23 13:00:52",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "DPD",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                },
                "15504": {
                    "id": 15504,
                    "externalId": "15504",
                    "application": "OrderHub",
                    "channel": "royal-mail-intersoft-ca",
                    "organisationUnitId": 10558,
                    "rootOrganisationUnitId": 10558,
                    "displayName": "Royal Mail OBA",
                    "credentials": "cbwMXR2z3oeu91Jcqi8PbLZwsbe6mRSmKotJNhSP740WFyH/xN+I8AzruE/RgDS0urTeMdRLX/EaaeVaVszk/SnJVyih4VJyOmskqrzzGVrA5iaPGM1nLcY4rwzZZd9DFNLYqEoSbqgJ141orsmCLVL2ApOLY7SpfePZywCmFnKlTJ3FDsSLix4kX0wjuIWi3QJYAMFahOrb6RXR24BVijZ/x9mniiKUBkzJZccDuvGcpRLcNWgnl//d/Hspnum7fmCjhcVZpeKcTjyCPlNKyrbwqtYYCyiADvh4zzQt1rFPR4DlSE6z87M85yPTiAr/1UWTohXOrE/vOPSZmxw5cOg6Y6weDvi2GDtu4CZvR2+Q3k89NyyAIT/VLGMugWmJKVf+aibk6dpd5E4URa23CA==",
                    "active": true,
                    "pending": false,
                    "deleted": 0,
                    "expiryDate": null,
                    "type": [
                        "shipping"
                    ],
                    "cgCreationDate": "2019-04-18 09:29:14",
                    "stockManagement": 0,
                    "externalData": {
                        "config": null
                    },
                    "displayChannel": "Royal Mail OBA (In)",
                    "orderNotificationUrl": "",
                    "stockNotificationUrl": "",
                    "stockMaximumEnabled": false,
                    "stockFixedEnabled": false,
                    "autoImportListings": false,
                    "listingDownload": {
                        "id": null,
                        "processed": null,
                        "total": null,
                        "lastCompletedDate": null
                    }
                }
            },
            "stockModeDefault": "all",
            "stockLevelDefault": null,
            "lowStockThresholdDefault": {
                "toggle": true,
                "value": 5
            },
            "stockModeDesc": null,
            "stockModeOptions": [
                {
                    "value": "null",
                    "title": "Default (List all)",
                    "selected": true
                },
                {
                    "value": "all",
                    "title": "List all"
                },
                {
                    "value": "max",
                    "title": "List up to a maximum of"
                },
                {
                    "value": "fixed",
                    "title": "Fix the level at"
                }
            ],
            "taxRates": {
                "GB": {
                    "GB1": {
                        "name": "Standard",
                        "rate": 20,
                        "selected": true
                    },
                    "GB2": {
                        "name": "Reduced",
                        "rate": 5
                    },
                    "GB3": {
                        "name": "Zero",
                        "rate": 0
                    }
                }
            },
            "variationCount": 0,
            "variationIds": [],
            "stock": {
                "id": 6945862,
                "organisationUnitId": 10558,
                "sku": "EXWHI",
                "stockMode": null,
                "stockLevel": null,
                "includePurchaseOrders": false,
                "includePurchaseOrdersUseDefault": true,
                "lowStockThresholdOn": "default",
                "lowStockThresholdValue": null,
                "lowStockThresholdTriggered": true,
                "locations": [
                    {
                        "id": "6945862-464",
                        "locationId": 464,
                        "stockId": 6945862,
                        "onHand": 2,
                        "allocated": 1,
                        "onPurchaseOrder": 0,
                        "eTag": null
                    }
                ]
            },
            "details": {
                "id": 1888931,
                "sku": "EXWHI",
                "weight": 0,
                "width": 0,
                "height": 0,
                "length": 0,
                "price": null,
                "description": null,
                "condition": "New",
                "brand": null,
                "mpn": null,
                "ean": null,
                "upc": null,
                "isbn": null,
                "barcodeNotApplicable": false,
                "cost": "0.00"
            },
            "linkStatus": "finishedFetching"
        }
    ],
    "product": {
        "id": 11400129,
        "organisationUnitId": 10558,
        "sku": "",
        "name": "Excalibur (stone not supplied)",
        "deleted": false,
        "parentProductId": 0,
        "attributeNames": [
            "Colour"
        ],
        "attributeValues": [],
        "imageIds": [
            {
                "id": 13812565,
                "order": 0
            }
        ],
        "listingImageIds": [
            {
                "id": 13812565,
                "listingId": 10222599,
                "order": 0
            }
        ],
        "taxRateIds": {
            "GB": "GB3"
        },
        "cgCreationDate": "2019-05-03 09:27:57",
        "pickingLocations": [],
        "eTag": "8f8fc5df0ebb20e1c3f34c66464a8689cc6128c2",
        "images": [
            {
                "id": 13812565,
                "organisationUnitId": 10558,
                "url": "https://channelgrabber.23.ekm.shop/ekmps/shops/channelgrabber/images/excalibur-stone-not-supplied-103-p.jpeg"
            }
        ],
        "listings": {
            "10222599": {
                "id": 10222599,
                "organisationUnitId": 10558,
                "productIds": [
                    11400129,
                    11400132,
                    11400134,
                    11409247
                ],
                "externalId": "103",
                "channel": "ekm",
                "status": "active",
                "name": "Excalibur (stone not supplied)",
                "description": "Wielded by King Arthur!*<br /><br /><br /><br />* we think",
                "price": "2.0000",
                "cost": null,
                "condition": "New",
                "accountId": 3086,
                "marketplace": "",
                "productSkus": {
                    "11400129": "",
                    "11400132": "EXRED",
                    "11400134": "EXBLU",
                    "11409247": "EXWHI"
                },
                "replacedById": null,
                "skuExternalIdMap": [],
                "lastModified": null,
                "url": "https://23.ekm.net/ekmps/shops/channelgrabber/index.asp?function=DISPLAYPRODUCT&productid=103",
                "message": ""
            }
        },
        "listingsPerAccount": {
            "3086": [
                10222599
            ]
        },
        "activeSalesAccounts": {
            "3243": {
                "id": 3243,
                "externalId": null,
                "application": "OrderHub",
                "channel": "amazon",
                "organisationUnitId": 10949,
                "rootOrganisationUnitId": 10558,
                "displayName": "Amazon EU",
                "credentials": "7KcoJhcMl9cT5rTLVAL58CRe2hSr87TkfK9zdFb2urppxk7EPnDoisnizeXmEKMFeYlaBwVoYJG/J8rIB+MwSKJkisYeQ3Az/egy0uwOs3UE7Z/UunXuA5xMRIZLI0GkzHYH0DW/67U6/zrSNdNuJ+QXgQLiKIVp93xExHup3yGD0YLHvxNSw58/RoWrRLpL+TXpT6X9VHfigokGHrIu4uwRnyByUyQHaAyeUGBxItx/pbeldHE1T3LJoY8JiGkoQwIUYCiE4jqoLHCwXqzZ9pWQG5LFNiSaGkA7WmOmTsWZbymL7Mg/YA7DzyjuFXS1mfdDFCRN512M1fshjF+X4psCnutqb+k8UgBvEHGedBJtQrfecrFRmkovlWD0Po62JCzpJAGCKIQ6IZqioKZQLEhD4J5AMcmx+CiVHku52/5WvByALEWvk20Mu4nthBHMnrc8whDd1HNzL+Idj6r6PaebVX22E1mb8w7pUUcu20lA9LNwcBoT4VDHHXtO5nPwD4vsPAUykf5es2vuEHZfhXDN4kdJ2s0ZS85dqxK53Qc8IsI19Bifb0Z4ofOFhFApFJ+8Bn6/Dcm8KahJzH76F2cj2bSevXflDLtlbSzQjILyYP9UifJncZLriu6mHKv+b4oshBCzIl03MdWXgoLUrHhgOiGB1jctzTp92TYX0iQwomQDHudS56ZaGGDBMlJtED3F6ojBhNBZQy+vIeBTcbIBgs60H7PgAjbPACtt/nN4zlw/eOFQfUEyWW3g9lqGPaIGwZJFUZeFC1F+DmfyReyoOqOaHhe9HRYWxFVx/QDjTZReHTHtSFIsAVX7HH8lyH8XnJqVKqdIztJWmikcoyj4Djj/LtcOFlktZFRHtZZFZW9i8/gKWj5LuA0QhlwgteoF0cY8KNMqYhpdghRggN3mU66tiXl0hSHLbLsPSORRdJgi4esMT7jXzvFVXhllCqycx0EkrIbnz2HTndq9b6lAdDTxxLIEPcdObHnOAPLPWxlmrxzxxC6922DqkqlK9bmkKtrTHDuN1DrGP2p+Lf3mdrTGoskYwVJ2x89mR0QnnQtQM3jjXst/ujwZfRKtEOMNx1Cwq3uqOSsCTt434A==",
                "active": true,
                "pending": false,
                "deleted": 0,
                "expiryDate": null,
                "type": [
                    "sales",
                    "shipping"
                ],
                "cgCreationDate": "2017-07-11 10:47:32",
                "stockManagement": 0,
                "externalData": {
                    "fbaOrderImport": 0,
                    "hash": "3e474f1d119d973829736ed9f7e18a699ff607bf",
                    "originalEmailAddress": "",
                    "fulfillmentLatency": 2,
                    "mcfEnabled": 1,
                    "messagingSetUp": 0,
                    "includeFbaStock": 0,
                    "stockFromFbaLocationId": 2796,
                    "regionCode": null,
                    "marketplaceIds": "A13V1IB3VIYZZH,A1F83G8C2ARO7P,A1PA6795UKMFR9,A1RKKUPIHCS9HS,A1ZFFQZ3HTUKT9,A38D8NSA03LJTC,A62U237T8HV6N,AFQLKURYRPEL8,APJ6JRA9NG5V4,AZMDEXL2RVFNN"
                },
                "displayChannel": null,
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": 1563850808,
                    "processed": 49,
                    "total": 45,
                    "lastCompletedDate": "2019-07-23 03:36:47"
                }
            },
            "12354": {
                "id": 12354,
                "externalId": "47fwg8cpdt",
                "application": "OrderHub",
                "channel": "big-commerce",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "BigCommerce",
                "credentials": "nYg4OGzKeMmDeVdlbKJRBg3WLTlxIWOK0or+WcNloytYBe91o1iu4DDfhTuCiat72+b+3G8yCA32SCXdB2+mzBxnW2mkSfUq73gHOKhQpNkBiA5dT+XYfHokcdHJErxWps+QtofTX0zFmINbvZOvmAvPpLdUblNMW6DI7IJ2wHoGFlo0UjB4b1PIZJ3HbgDZRD3RQWCIHbXgxpuOAKAJx6dHVtnx6hxqVGMrXB2CApU=",
                "active": true,
                "pending": false,
                "deleted": 0,
                "expiryDate": "2018-07-30 15:12:37",
                "type": [
                    "sales"
                ],
                "cgCreationDate": "2018-02-19 11:20:51",
                "stockManagement": 0,
                "externalData": {
                    "secureUrl": "https://store-47fwg8cpdt.mybigcommerce.com",
                    "weightUnits": "kg",
                    "dimensionUnits": "Centimeters"
                },
                "displayChannel": null,
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": 1521127671,
                    "processed": 13,
                    "total": 13,
                    "lastCompletedDate": "2018-03-15 15:28:15"
                }
            }
        },
        "accounts": {
            "844": {
                "id": 844,
                "externalId": null,
                "application": "OrderHub",
                "channel": "ebay",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "Calico Trading",
                "credentials": "iCpnvOwePsMJq7J40bqlO77erZ5X+00dzKvuRk4PpSGCEsEYCixvrALXTh8lZ4anCsePIJMLRFc8MN0C2DNL7WWBffE20qfU4ZmfY6BtJjLVpXn3Y8/aLas6uI7BYX+xydtAavkSmiGJOLPEtQZqCpHT008zPFTA59ebB4tDe2DvZHIZAPoxMX+QfpaaujyBxpzw3RGmId4C6LzUJ2G5meV8tzw92/SMU5alnWCrX+p1LUK3tk7CJRFDU6PSOn8Lh8ZegQEAoMUGMEOCZuIvhopDmYiCm2PLvk1f+IofZXTufQtAjZBS5yyDTVqqKSS056zp02tyh3J0aATDFpVONkJ3IaTFRNpH0eG3nwwsI0RgaRPTNVr/c2Nhf/KblTE0P8iOus8UJZTIesgXQApt2yvUr/P/X/VD0gkXZO/nREmdRqAerC1Usx5mCLvAUBYoNo3el8jsdYFX2ykzbwFd0cHJGaQPujEdjmR4ELs/llTelUGT6v+MIrfw9cZQ8SrP2OziAP5lsrr9tqi9xG45dGas+/jCOWdU8eAxm5rcQEtDlWG1Kk74tbwWFLqMgrKIVE+yx5Xtud+cKgEp2IDD+4bc/7plEJBW0XQ6nMJPelfKq4DnQe4vw0hcgyJjAzJFyDQtN0xVlOmciVHRi44PTgEFKTVUmwBzwvxsNeUR1an5qeZ67gOxRHgndI0QVq3aKI8vm8+1arW1Hg7iYYbdoZ0L+Inl+SGRdQFVwfvgmLjV9YacJV4o/m2X/RUawj7i386r1HSitafwnICDgsOk/psvSb7phj4Z/2jxx+E5VjlW38v6bVpk6UYuGimbVyh9gqNGq3oX1rRPG7jAiUQTGIoSFt56BJFAEyDMXqNnzv3c/nYm+gTw40pmhPPAUMb30ZMecHdIG5ulqgaQaxADOM3Lc4VddBhFO9ejdIrACf+Az+TI4dzGgDnh/62yrS6hkdP5qR3N1LUQmyEgDH386oy7uQsoe57Dnuy29YNI9ijjC/3Zlf0k/O0SzqFCKGDOTOWPDA8yj5bw4ZnwyWE0Sl7FF3QshrhdmMlJ8hZz6oE8M3J8ynNPNzHl4k+ItplWSp+tnOgMv8r6CZ3/jvW1vfUQ1z2kzw7g8dt6NcQjFTbCAriDdhJPYTgeOtDRwaWpHuFrezA0suhYNVo/7CUyGzkOk1XFpMazNlBUKDFHFwGAHLMRLTKivg0r/8pQzoqROxUjDedGs8YXQNRAkQzdQx2cTEwW4yJNrEn9j8nFD+84l5j+xKTQfwkbfQ0AzVBO/psTYA4PAZDArtxqxiTroiMNdaZ3P8vXDpojkardR2QKsQEEoInXaGHpNzxLVdnrZcbRBCZMaWacecUH6H7vE41PAnslbm6E/0h1gCHK2tqYCLH1M/iYTL/hp64nPlPyCb3P0/TGu/gFcamxSRqPF4cP/MnENAtgIW9UxRsEEUbMVSvYxg9MtkADggF9pmL2L4Crkj+FbTZ7+yhRxhU2ycwbhZzoEXDOqPauxnDEXIbXlV0gJrUnhwIcA0NQi5JkyZukM3HjvWX4j/MB1mFsKlA0wdfVYmh8kIFr6bLCfjuipbC/sUIB/93U+rvSGiaVNqM52w6dJjIQZ+p9eDJzKyHy5JIipPRhCcMpBx5xnUA9rlwhOhy9wKzxRfUQApXOPu2MavivSO/8cP5mLdkylbH3T1vBBcuSVcHhQ+Wvhpd4R1zIAt8EtZyfSJgsiw3EsQHXfebAoKffXQNKX63T2bXJi4WAOrRYjPAsey+YmHk=",
                "active": false,
                "pending": false,
                "deleted": 1,
                "expiryDate": "2017-07-20 12:16:35",
                "type": [
                    "sales"
                ],
                "cgCreationDate": "2016-01-27 12:16:36",
                "stockManagement": 0,
                "externalData": {
                    "importEbayEmails": 0,
                    "globalShippingProgram": 0,
                    "listingLocation": null,
                    "listingCurrency": null,
                    "paypalEmail": null,
                    "listingDuration": null,
                    "listingDispatchTime": null,
                    "listingPaymentMethods": [],
                    "oAuthExpiryDate": null
                },
                "displayChannel": null,
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": true,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                },
                "listingsAuthActive": false,
                "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=ODQ0",
                "siteId": 3
            },
            "1096": {
                "id": 1096,
                "externalId": null,
                "application": "OrderHub",
                "channel": "royal-mail",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "Royal Mail PPI",
                "credentials": "Royal Mail",
                "active": false,
                "pending": false,
                "deleted": 1,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2016-05-25 09:55:08",
                "stockManagement": 0,
                "externalData": {
                    "PPINumber": "HQ12345"
                },
                "displayChannel": null,
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            },
            "1445": {
                "id": 1445,
                "externalId": "1445",
                "application": "OrderHub",
                "channel": "parcelforce-ca",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "Parcelforce",
                "credentials": "2+p/GVQs1ndg/heKwGT6bePmrr9ElapPzIhmSYdggFDPypxFY+/sIyYl5nWNhBpugPdB/rWFnyon41Trir9I1tPLadwkG3tx4nXqeN1Fs417/NKHRZtZw2pYcLAOYiJO5egBD/wtYAVOWwTie99HiBsOXxjuOifLQ3/eoo2lgorjmnQeRJ5sKY535YOsHS3m0F47C2ypo5emUIw3pXCoSncxdDydOmrY0H5tJLUIA9nGZ7DDuNBQyfFuu97XsIExuriMw3qIg9MXPcAFy56silpxXdE8qMAlIN9NNJQqlcSOt++u6XpoeO6FEHXmvc/186H3Pi/XXwp/xpr7+0Y8FK6K0/rPga17hGWRLY+AidVnNyYl7qc1LljcEmhSXD58fpzMIOcH6XRjiV/giHHZ4EqTKBMIBpxwJ8fpqpJAGAlGs7t05vol/44LQ37cVzNp",
                "active": false,
                "pending": false,
                "deleted": 1,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2016-08-30 14:27:11",
                "stockManagement": 0,
                "externalData": {
                    "config": "{\"emailNotification\":\"1\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"1\",\"sms_pre_delivery\":\"0\"}"
                },
                "displayChannel": "Parcelforce",
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            },
            "1447": {
                "id": 1447,
                "externalId": "1447",
                "application": "OrderHub",
                "channel": "dpd-ca",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "DPD",
                "credentials": "ljkIEIyzleoeSE6GrLzXJXh9nlRkVmiWu+tEm023Qsld2iu0461qm3LK9ZmwxZ110Jh/PBp8E1hUuUd61B/7cei8QWZcF8qjAq6IyZnkL+MygqJrScSdbowuiFSJfsw2oKiNH5pkLZ37HMyi/s4bNkCTOCzNIF+QBeWDX7GEXwXAkBhMGUIrQcXrjvf/aJV6+9D2Wv3TZqXRrZHg8HYqL7KJm1f9FGQ5H6Fxsn5Ams7+qTcTfV4nxKB7mM2aQxLbPF2rz0B5UU4kKQgLjc6p6ISTm+HRkEPqo+TQMZU9diBQOlrEm5MPBDK/y/QKZf8SqtEG7L3VKSw5pbpyThRUvcEeWsq5eW+r3zQ1bhzOewYHHD3psQWUlWlWC2+ERO430xrYDiihs5gOBhtG5rYI15g5Hz7GrRSPXTJl2KHeOrwTUnKVdmgOTYFBNwiXB9yHAMw79394xLhEpgeoZAon59z+n/kgCV+xf3164Up2DNB4ZXeC0bKCwZS5UU1aqGV8imcBrsh45MlaF/jDeRI+ZoWhOUjGdJZrqibPhAKnOG0PW4028tQ7WUwl1Q8qZ10AQRqQMTIChoiTVr/CYJ+P+fW0redHDDXzi2jSa4sp9sPnsmkCIP0wuOkZU3yxawpi",
                "active": false,
                "pending": false,
                "deleted": 1,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2016-08-31 10:08:30",
                "stockManagement": 0,
                "externalData": {
                    "config": null
                },
                "displayChannel": "DPD",
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            },
            "1448": {
                "id": 1448,
                "externalId": "1448",
                "application": "OrderHub",
                "channel": "interlink-ca",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "Interlink",
                "credentials": "NhqQH5Yvo2mTPMAitLN8XOZRGJjNKbu4ld0zvfLH195fntGbuiTG++69OzEB8uB0xUkNuWl26t4ou+Xl3A3pG8Nj4doKnuE7Tnztrn82wVrGMkJHElVGs06ffZdFvG0s2MKehldhazxC4ycuEbjDX/AQZkOTULoat+XeDeujvZnN2xxB+o7xpx5FOjeJTyyypjoFa8MEtGQovHpCPYO7ph/Av7MU15q9doddvrARtiIEK987xXMSnei8Q+eauGWUs+74javCusSj0u5QKPLOoph/iUNtsU6XPuMgSbUvaNpQBIR4HVo/ztnXlOx8JeNC+TrnBQX13N+5I267uYhMNpZyh3I4jj2IE44WeJvWCCBCw+68U3UR4DMccBKx5ClJkReekIwl9D9KNO+dK1lEjL62B9peb1JQ+RgFeulo1XB4otF/cJXD9MeliZVDF8P2rR3v7QhyHfpMPQdOu8w2/blnjpu1PUdUPQhUVACqPNJjYpLLSeSWZjZaTENJs/lTTmOSUAMYMsVwCNAWQL8zpgxNvK3PmtStI9g4uNhRPUUgt1d+L+Pu/wSqkVhqQ24YbahGfPHKCC09QODqBBkgaHk0IlcVIsKLOJ5efJBCP79HOpeN5ZvZpBhhni+yAhDxeRlk996cQJGl85xiVHGgU6Tf1KycBa+SWeKj+y90s1aKVU5yLhEJL+DNeq4vXHWMt5KvQoA2si8GMUoKzDnP1w==",
                "active": false,
                "pending": false,
                "deleted": 1,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2016-08-31 14:28:29",
                "stockManagement": 0,
                "externalData": {
                    "config": null
                },
                "displayChannel": "Interlink",
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            },
            "1456": {
                "id": 1456,
                "externalId": "1456",
                "application": "OrderHub",
                "channel": "parcelforce-ca",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "Parcelforce",
                "credentials": "X5d7L4B6DUXntVIsKEC6J7ULjviSYN9GsxICofFbFW6PswrPlEmAdeK7IU7ZjFrFRTPaP7W6e/Iz+jG+KqKQNCLVF+B2ggau5v22zwx4KGTl1+9TYhkfhGHXhA95m2l5sVBSNOiSr9ly/kprrBXa7l22ouWiXYXt2Fzcx2VbDjYd4zAIN1Tp5N80alyfkRzVM/RoQJ9IwFVoFHqMXE2FVPUz5VAriZ9LM5DTJHUYuS2bZ8d+s8c4BOzrwi7NZhEzzsbWtDF9gKNRKc/wqKW3idSMPGvSJTnjCGMH9+7FxHXhYN9BE/igqnluhIxUHttJ7A4FQw3yEypyDDybfJzta54pGULumsMmqkBSOZ69YgKCrYpgxfZdhfnzmy8hIiAwoTOZVsgQbBP4rcbFyyD/O+pXGuVh3IDeclenPbv3i0jMu0SsVFDwI5QcDoostNQMbhCe/+nuTvREI1p86aJyAA==",
                "active": false,
                "pending": false,
                "deleted": 1,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2016-09-01 11:32:39",
                "stockManagement": 0,
                "externalData": {
                    "config": "{\"emailNotification\":\"1\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"1\",\"sms_pre_delivery\":\"0\"}"
                },
                "displayChannel": "Parcelforce",
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            },
            "1457": {
                "id": 1457,
                "externalId": "1457",
                "application": "OrderHub",
                "channel": "dpd-ca",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "DPD",
                "credentials": "fG7mb7o2273LKUcn60vpLqfgxMp0G7LArSWNJBhjacburSEe4dIIXIwv33UPngs02UrfSOtf6YxYLQ6+efXNa6NbF4r1WlcVlbsQq4kBrkPtHWnfJE/IUgj6FNC0p4vqMB/3bwaV6f/gJgkSeTMmTGnRtr36icakeFbgOG+n4mBJhMpH+CMErlhJnO3+7Kq7PoAaA/1EZyHSf5hMBnrU4ZBrFEaGChToDRaiZGPgAiFWs02BlzVXAFLQou3FD+UauH+zbW1kRXCd+OOYTG/ew4yPNPB8SC3CCHwci5QiESVIs+q/qCApLMBVPVq6/EA8bghNsO7VllIRhUqNaHC/X+K9IePaplS38FV7nNd8twLayj0Fv7JSNqD8BwgVWM+p5geadxX9T05fQ5ijqfCP3qablNY1hJWDQMnxbvhExxjSO0BPvaafYOHE/HiokdsCDjLiiBCa4q48O/tiLMgaR0kjpFmD8xcmZj5+fPKTCXKd6jssI9pTEtoon9dQhCo0S/kF174ke7r6vj/9lKr2rTdVGlhNoqhhxNet3AeXppMk7PZ2JxpiYFQIy3CTuCs6Cce4c3Gdn1Ws/iSZi/9PpMhP/hvUxYDO6SMN5AmI7S0=",
                "active": false,
                "pending": false,
                "deleted": 1,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2016-09-01 11:51:43",
                "stockManagement": 0,
                "externalData": {
                    "config": null
                },
                "displayChannel": "DPD",
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            },
            "1458": {
                "id": 1458,
                "externalId": "1458",
                "application": "OrderHub",
                "channel": "interlink-ca",
                "organisationUnitId": 10949,
                "rootOrganisationUnitId": 10558,
                "displayName": "Interlink",
                "credentials": "xt5MOjdt8njG8ACdxh7Bj0wTCGxMqU0wJXIoG8bM/JuUSSpCM7P+/P7OhAjROM1jnbeSBpT9UDmfgc23CaR2kW/ebcVqrRMwWsxDoC0yzR/adLgTn+TnV7JqGWYV2Te06IV9otvmWG30mOSvrawVTXM956dun/Al/hUAM2E8CJFFnG9nG11DKXfa7CB9X9PeCGGHq+YRuK/n7xI+s7WblT+BU1YSIyhGiSvzKCYIrNtNwDjq7m8RqDsCtYNGAUAufF2pACZKU5L/YF7ClH+5pzwAFalqepI6GjrnMkO5gIMHA1qpoiYBDlSdctRGIeteCz52n4vvlwHAhWQKX/URUiRm8JYUdwCKcRvKw7SuYm4DN4nEnjA8oVNOA0zvMMtapgvuHQDX10NJ3Zwahh8BLQo2XvjFfi8uHkJRYP4OqbCULWard/8jBosdZOPtXJFXF0ZGXuxQEm/vjNOfz2wOlhXAK8ppZsm3YV3xDv8cIglkWExxs9z20i0IBQYjON6xmJLymqwqBmWFo9AK8KPZ15pmOoWHOgAcfpKduqUbNoTNEfnLkDz+eYnpXvWRj4jy5myjKUoi/QaLBBBK0G+bH+61cAgsW8bwaI4Wl1+Hqc8OlWc7a+mrmPpSNj7291kr3zO8oM+C1SASiXjo0oqTfiGiC9jeMSsLRiTz70gLn83dqaLkwbtvFsI9z1JPVA27",
                "active": false,
                "pending": false,
                "deleted": 1,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2016-09-01 12:01:39",
                "stockManagement": 0,
                "externalData": {
                    "config": null
                },
                "displayChannel": "Interlink",
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            },
            "1517": {
                "id": 1517,
                "externalId": "1517",
                "application": "OrderHub",
                "channel": "myhermes-ca",
                "organisationUnitId": 10949,
                "rootOrganisationUnitId": 10558,
                "displayName": "MyHermes",
                "credentials": "h1S6HNiK87jLSSQ9mQdgnFR3kwFgymBbpYfKOKTOqA6W2GDiHqs4GRc8HFPlDPA4quy31sQY+v7aVieuGjrewV0mV7rExhiVhpnPE2e6YOr1OVk2FS3VVfVSKKhcMy4RVBppxlE1hPgW+Mwe8WHtW10AFemp62BcTQNXsSIzfMwNJVjYpm3yZklFUMWUiUMqJAsyi7QkZUKVOY/z36k0FVgYPMjeq+WdaUm4T8jvmVXJtLJheQjpiYD8C8vFutCMC5JwCEAOJp0EPUiniz+FhkOI7b3s1U0wUt8R/aI4VD+R4JnHhohsCJHfupcz9xMQbc+3FeociXqZJJ8JLHZSRHE1g9FcBCzHHAMaFT8z3Tc=",
                "active": false,
                "pending": false,
                "deleted": 1,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2016-09-19 14:43:13",
                "stockManagement": 0,
                "externalData": {
                    "config": null
                },
                "displayChannel": "MyHermes",
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            },
            "1518": {
                "id": 1518,
                "externalId": "1518",
                "application": "OrderHub",
                "channel": "myhermes-ca",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "MyHermes",
                "credentials": "KhhFj5g00yulMijSIH+AIhJDZOKhkCOvK8bmDmK5ZiL08EdLU1JvIkoZdb+/nLDwuv78t6Mkh7zjHnj2QyrDzVBYmYp1gIP2otSCu84PvwFEOgRIEGKXIp18kwYHMBkhE0HryaoBVwYnqORH5/vhVz2rmUl3q33+6F9oeKIEGziK5vqf8TjDXJklCGCahkQe+zjY1cPzQc43pLaTI8meQ6i5Fc2NtMglKrStfE3sysmOH8Qw0aNHzDs0R6egbZvvxbvcYDl3bqk6qpllOE6dqUTYu5OSkYXN5ckY2BzyuyjgpF5Qbt0ytCFp5WhngpdcsAzPBSJsbxLi45+KvUcnBCrtlxCbS+0kzxyj470rTR4=",
                "active": false,
                "pending": false,
                "deleted": 1,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2016-09-19 14:48:47",
                "stockManagement": 0,
                "externalData": {
                    "config": null
                },
                "displayChannel": "MyHermes",
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            },
            "1519": {
                "id": 1519,
                "externalId": "1519",
                "application": "OrderHub",
                "channel": "myhermes-ca",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "MyHermes",
                "credentials": "9ss6kvrwHKcLDKIZomeQeosQVceVYY83oaK2gEN9EWvxyZBgc36LXjeFXjzCqNdZgPJova1NC8lsOmxLEQ6rQkqJQ2ORcwP61RON3qtTVmOtbRodGWI8F6Tif5l4JVwN3V6U0eYv0HeJIAZS5P+y+qiEWNteN3oMI5s6O2Z5ROpFJm4Wmtr+mWhJstqHXxzfVowEr0jgzVV9ovv/I3ovHrm2oR18pJpQ8F9hmbKlWS8Mx/tsuprfKDXHB6yY0TtY1A9rVP/yVR6idBExH0WovwBSiWH/w55ZoqTdrNMvlnIIdQ0VBFezjqlg26DQQsvfnbWToT771KD0iskq+2HTY2vwjQndkoFFQXO0aX0Gr9E=",
                "active": false,
                "pending": false,
                "deleted": 1,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2016-09-19 14:49:22",
                "stockManagement": 0,
                "externalData": {
                    "config": null
                },
                "displayChannel": "MyHermes",
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            },
            "1520": {
                "id": 1520,
                "externalId": "1520",
                "application": "OrderHub",
                "channel": "myhermes-ca",
                "organisationUnitId": 10949,
                "rootOrganisationUnitId": 10558,
                "displayName": "MyHermes",
                "credentials": "CktJet0qBgjEGaDOHPUDWB6KCpZAtwnby4eUv3mGBDbA/xa91TRzfWZp1W4R16wOwB/A5EXvYZMpxQZg4XtdRDnfapKH+QpL6zD3ppFnXlwU51dBEq/X3ulpR2VUSPoxzaaKsFW7BsopLwlNMkBg6XKPiy/VBawcdGocWFgZUEuptaBhywgJaX34BV5ozh3aECMrB7P7zHfG2awMDizXerCg2zjeiSr4oTiL1ohbMMMYoA+dr5JIWrCpk+KIUSymEkjgeHS1eOSSr/XqoaZ8RrB45XVYFzIlOXEsGydlGA3VTVhCNE6E6AsLmO0pWopCSx0aQDc7oUk04KDDFaGSg/i3aFMIxxL2s1RzO+ucQ0s=",
                "active": false,
                "pending": false,
                "deleted": 1,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2016-09-19 14:53:24",
                "stockManagement": 0,
                "externalData": {
                    "config": null
                },
                "displayChannel": "MyHermes",
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            },
            "1822": {
                "id": 1822,
                "externalId": "1822",
                "application": "OrderHub",
                "channel": "parcelforce-ca",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "Parcelforce",
                "credentials": "S4gu60+S+paiPJMW7SRwvl1pT6QI2trrbjLFXSU0W52/RtzGLT10+TbGnh65r1EL8/imaGzb67YQgbU/zDCE3v763VAP7gKfrq6ifPlHzluRaXQteGpmKzQQWPp39q6XgTzzAANLI2otAzTrQjZXYY9fUCaYdGyR2QmxOdhfZlbJQBq7cmvOHk08fPy+3DYc9sIGnOXLLpdS1rJ2apJWY03oS6d9DLwXRvfKPrwHW8mofDFl+WK4gZyRUcrlLTp2v2HrzDw9TPkqs7chL+COpbscgph4soytOYCrl/Tq2gAqjVjoC4xaUCzrbZ1RY8U/GpxFcwvJW0Gi6ZgU+4UEYLFeGa5He61pExi+bmwp2Wbase4DJjfipO4anqqwySM4iC/xjKJMg7mD8CLWoLHqzsYw4ZvrM7tg4pQo8tBVDlidp7S+DVg6nDMgogppJ4XbOLW+/62n18TvD1DJNSGLDQ==",
                "active": false,
                "pending": false,
                "deleted": 1,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2016-11-16 15:41:22",
                "stockManagement": 0,
                "externalData": {
                    "config": "{\"emailNotification\":\"0\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"0\",\"sms_pre_delivery\":\"0\"}"
                },
                "displayChannel": "Parcelforce",
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            },
            "1823": {
                "id": 1823,
                "externalId": "2015291000",
                "application": "OrderHub",
                "channel": "royal-mail-nd",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "CG OBA",
                "credentials": "hCrj92WJJBP/ZwLM2YAsOUQpIJgNNHR1miGevkZ+ojdpUQaP02BEdQnku/fS+aZPgK+iCw4eoJuAD+2qY+FuIL3BC2jlfZCvlJ77R+bke8Mpp3/iUSFCenaWyxSbCpId4AsgilA75jx6vp9iJb/JfJ4E4ptVX+xXKkbasftz6zahmX9ZPGyQ5xF5EuPoT5jIPi+1Nrn7NwczmdUgFXYELPjeVCV3Mu4+Fktfd5u15sL0IN8N221wSo/iXsdEb/JkXxwiyyNaUJpplsdrRTF0tMRaHj5iz8NbxjzL/q7DZh5E8zHHGcCbWoQ5ZdRaUWEA8W6qF3Snxk/Q7KgHdmFYPMl82/MFAuqVgJN8JDVKSGpoxdB6Hew4iC1cibJOJYJbyTS/j8VBiAOex5jLRjwGpiX8cK+7tsWRdhcP27uX8SgZqvvBImyH9kp901/V5HYFiDGVJtd8j8zNpTVngEJ9szBrFVKQSrOvob4ZBCOLASpNP47CrYYmcYXcuO0hy1jGvSL7FFXZaYGUXFcZsZ84SxpgZR5GjwPqF0MctEs85xw=",
                "active": false,
                "pending": false,
                "deleted": 1,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2016-11-16 15:45:10",
                "stockManagement": 0,
                "externalData": {
                    "accountType": "both",
                    "formSubmissionDate": "2016-11-16 15:45:11",
                    "domesticServices": "",
                    "internationalServices": ""
                },
                "displayChannel": null,
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            },
            "3086": {
                "id": 3086,
                "externalId": null,
                "application": "OrderHub",
                "channel": "ekm",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "EKM",
                "credentials": "gwdBr3TbEXYietikBI+mOpc/BS8iOF8h5kCjQkIjHJQteqPeVH7Kpb2PHH8gA4bUD+nu2YnmfNKW8BNqRfvrwxX0jGIXBEBpk/BXbyvuu0KuYyoguq6K3iTbaM7awC2acUBeK5SpaRSnGYB3zODVtFY/6neMK9b5fQOhyWm2itMphSkEicN/9g6z8/Q3myo/eT7Wj1yf2SaeyA1zrp+MwzrbiVt5/800uYARkIvqqu1dYQdKpKcuHH3a5GA6MLupbPB/CPHldaWGnv2kIdNWiWz/6SVSJYI7jmru2Qnvt/mdHmFHjXXOvNl0b/bZoQaEYm9xwCPC6+14hS4bsFnFqBqoaDnl8/1PmPXoOofQ9WQI6Tuhncu0xVJIONdIN6zhIpCtKK0KylBQ5OBnusHFDUhy3F5WFX+n3K6+WbVWbNWOCqmDOdePsCWM9pTBFvpPJkmHeDeuKfu21by9Gpc1KnnwKdUcmWX+X+8kO2m0mgs9xOrlJ7+WC61TQe93w5/QIIPRmC+CjVhOawZwg16M9U89k26aQMoEWAr5PA/MLIlElw/mVdlNwVYig18fh9hPBlJMcpHh5YTFUosyj1pP7fcDImmodxyoH5GbF2elwB11sfNyALsNFvz7mDBJt9Bec3piOaS3mCGGkwbbuUMSWhbpX6Gd/7hC/ZCp0lvon2AXt/pNAPKZYsPURnShF/D/SbVMF9qDFO0Fd6o8dwsUAPFPIBvDl8MfBrqs5VKa07BqorFZ49QiOuR5jTKDAWpY",
                "active": false,
                "pending": false,
                "deleted": 0,
                "expiryDate": "2019-05-31 22:02:04",
                "type": [
                    "sales"
                ],
                "cgCreationDate": "2017-06-26 15:12:05",
                "stockManagement": 0,
                "externalData": {
                    "ekmUsername": "channelgrabber"
                },
                "displayChannel": null,
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": 1560910068,
                    "processed": 392,
                    "total": 392,
                    "lastCompletedDate": "2019-06-19 02:13:15"
                }
            },
            "3169": {
                "id": 3169,
                "externalId": null,
                "application": "OrderHub",
                "channel": "ebay",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "chosen12010",
                "credentials": "UA2QeZxpw9ep8cbz5gSaMPOI2S682+fB8XnO7q4128yBxcp2d6tlGt2M63SuwTLOnC+IZv8K4nWetiE4o5APCqm997qiPh1GnrNxcwA5b5uhhDOz2jFcSuJ/F6Yl4QjDQLgPTGwTuoVnXob88cO2d1SLEpHkGjXEtR2XOMi9RNW85FyfQpUtzdO9GmSaExjNiGhPGYeI584qaMJxCbAQ+CPLq8i+9Hyg1L7lwdPONSVsDGruBrDX3JeAXdSEV+MD34HWzyAWqqU2NePseh/zr9ZVLBzXj4tKq7su0yrpbq6knM8mCzqZKom3zxeC6rw7R5RCHDrmP1HkzvS0PpGjWz3kx6/9GJEVIMMWJ3JqglQDiWoBalXT41lbtxFOuI3140msja1qdmavPesf5ZAwHq2ryCCtl7DlRLYb5m2EJyH/mPYS0XP+f2n+DgDNXtQOtclPyR/olO68VClw+AoQHSDnqyh3Zc4WjmaLhpsDUz/5PBwp+dm1NQhTlzZiEXk2RDCScILu2ZPddMOclnrn2a4QXwQGag8AMlq7p4sBMU9MLvO073YdrzcVvxNaXnxAoaIZ1WD+W6OdA4SNjdmlNcB2gR4pNjm88paG6kEd+SGGgTTVddA/fee5kS0OtI1S/ydgGqjLsPYIJE+kTcEFc1uAOkyRVMNTN3T2zwjeleRsLTZ4PkXABDokhmO/r0mo6dxjThId+xA7Sq6Jg7MP+Tu6WveS/UbjQrtC8NicbTgxPzu5Xa9rCcK+/HyC4zwdFaCvC3MQSaGkn9MvsRcVKQklQlOISZaILV10B3/4YM5JsDmDIZr8V2hYefC7JuvVXctaiGDRbsw7Ju58vTi65dNXA0myisoNDR0cai/EvNwHszYzaocCdX5af7NXaeCxX+yitu/J5EPMmDDEiFAND9Tsf1wf/bF83bpalEpKsAaSnbvn5RE+6M8xC+oiW+At8zBLEK8SZb4fzfI5sj96Eu1qmpHTYnAUxvCzcHZmnZAlfmji6t0EfxSe0NFulzHKPPcuoUzgFFofrBhDaDXBteqMFquufFm7+vAp4XsVKgA0yAVdfi2U6niUhhw3G4D5zDbqyoKAkNDS1gnVK42sAAInZQGimQo3xO+I3nNECsbg6eFSWXwHkfgAnAq+neVZjcYm6o+qWv8To/MSw6hWE8yJc94JDu4nGKUFLlv0xE4D31sozgIYDhLggqwTAYyVIzNguxEDBo2t3cCcI1UF/+dgKXrZ3wiV4YWZJJxz9MKcLFm80P/2RITcQN9W3eVDN9wX0XPJsMYNyJp3BTbCdifrOk4hoF87oI+IeM6369uLIq8LN0x7ZHM2+GrZfXh8hSBiQnV9H19JI32/45QXaR0TU1BGVWXENy++mudI8F1ear3PDtUvxIg1kM+qHNaGah6braiv6XkYRLlH6b9YYk0BPQjoCwxyQ4HIzs5XLhr4lRoXA1rxuNeMRwV5tT6gBhsELw4vdVzKDDwhBSPb0ei/cbqc3oj9iYppLI8pwGoCQn/vBqDJaRJHoJcL9ZwE4CXFqgegU3arIGXqSFvxqlXQX6Uu+da371pu5MftsABT9j1yJSrXvfGxQrISF5SIz8gzgSHa0o5rZweOaqeq613Gwvsi8lpdMwCUX/kTGWArxeVZjySm/g28fvUgztpZGkAKcCVnSH2bzvLcvaqP6X3ezUAdfG6y+Lv/dHz3ZUFBTRGU+UN3JYDcL88vCkKrrOrsiXSVlgpo8+chOtnKjxnJFYnIWvLj4+qCBcD1NPw=",
                "active": false,
                "pending": false,
                "deleted": 1,
                "expiryDate": "2018-12-27 09:19:37",
                "type": [
                    "sales"
                ],
                "cgCreationDate": "2017-07-05 09:19:37",
                "stockManagement": 0,
                "externalData": {
                    "importEbayEmails": 1,
                    "globalShippingProgram": 0,
                    "listingLocation": null,
                    "listingCurrency": null,
                    "paypalEmail": null,
                    "listingDuration": null,
                    "listingDispatchTime": null,
                    "listingPaymentMethods": [],
                    "oAuthExpiryDate": null
                },
                "displayChannel": null,
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                },
                "listingsAuthActive": false,
                "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=MzE2OQ==",
                "siteId": 3
            },
            "3170": {
                "id": 3170,
                "externalId": null,
                "application": "OrderHub",
                "channel": "ebay",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "eBay",
                "credentials": "YnFTJPtcN2vdvLi3beaLxVmry6ZRq1g7FaltD6+W0fYrDGon1EY6sPOLqIjyUz7LfDd4GSfFV5X3svlWdNQyFNqWO/0nvF+Hte/I+wfv0C/mKOYW3yQ2cfdiTqAcsTkAFZC7gmnS0f42KDKntDyqNqLYvfoMH60r1f5z7GFTlIeeJw0ewRw+Uw5TmveiAcb9Q6NPSycQNxnK7zeAOUvZZ4bsIVWdEwS6wX7K7oP/zjMlSdPau7+E6BjrqkuyfNIDS8Tn9xKvwmigNn2yu1tp8WbrvXXonuAlBYZcmVX1nXwdvw9sMyRQbV1Zm8HB/tR9DloBjadUybCl/dlWFWovHd6xA0d3vgXLDOVF5LpBPLFlOmaAKp24f4Aw35vR8qMW03A6+s8jJvdSBkepwrvlNTiK3RfAx7np3Z9aSBA6P2BVpUrXuUvVwFI30Ub7jLBmIjIIyTsHOIpiM+XPBrQv3g6sdm2+oPh/5k1F+M/6ZAM5Pyml+lgnqEiGdF54EXT1cZiosVxcThC8Z5cg2XmbdU2ZHqQwhArbzZ59ne1moullk19yGywWK3JVeGhy87CHqyyJGZeo1MB/DAikuW0t1Aozage5nhGfkiBzcsrRT29PVyFMGKMWCqNLJQ56dXkllbwd6HuKsxZwGTwnaqJJRWE8bRpaytOjAs9TyUA8Ojo7/+Y4T7ozK3kbP7RMrPeLFFM4rBCCJrsjaDHM3IQOlAQr9AbpOwrb7faBtC22xdXLW3l+WDo+EpulNQ2gNdiyMO9pBinfARuW9UblAJRosEUsw/tgFM9rz55YwVFQpPaMFfJe2EVVWiXsbNlIowvvDARBu2CDm9Ti9my18LHYkLq66NnKiqSwiK1r9fT8jw3nb+UtdTvLXgRIeYCkGkxLcUUGiIdtcbPLdC6U89kmNjcnoTyl9gJQ1q1WzzVGI8FWIy/YLJBGTTRy6728mFnlWrPE6JyCDAidb4V4RE5BQNLFJIY/bICprRoLNUHrIbhjiujhcU/P12NpxtY00r+FdAJmxMO1LnPl2QnNsG7pfEu093Mof25j/NkT973TwdrO/yOsd8sR9KxhzmzKHGl5l3Z2QDyMO2Kc4/mxwUfm6J1Ns/Z3K9eWjLDntck3302oC1Hcm7sVTx8xJ35sTX8VzvBBdspWsavyDs1fCfvwSKhHK4R1zpTTh4a2ZEJV+M6BroxsELe93/3mwTKHhVKK3U+xsLx7LevcRdDpIo6rcP2wylQeyRXsw+d+tw6bb7RTHL3D7Mt5l3dWDpV3KyYGzEQZao+2lm224GvtZd15Ey1FCOBVi/ks0+VYK5bOaz/bPLNDVqCW1deOujg6V0kyHm6Iy0LROngS38G2ZooN8gEaXbUdE2muq7ORC4yXgs4diVPuQo63OubPHMLctFNU/LdgSjXGFyGbzc/TBMy8nxMsijPtVbBCt3A9oUFeIubRLlP3OkXmjPrnNiLQT3NUABFOrDiIH+6DA3fNu3+8o1JDqWcOKvQhRwsRXskuJ5WSpEW39vg8M8dO/F3V6uYe2ET9t2bQhis1CuZmEFo4EMghZZE4+6pco3v+wCTi4plbk/Hf0f9MNUHA8MiGMkCW+ZsNZX5mV24UGbGHcPG+D5LyNPIeUYJTto+yomDArATe+xY1m/PNPgftBqEngESjbv06xXwL4NI+74nch7KDFuWyLLGmZxsisd6u52jybidgkjJzUFrq4fPRvDt8P+XLBjloFlBovlaOjeQGEqJ+2nrBvGBFyvYGrUxiOmXab/6B1o514kFuqz3hQeu4UZjy8Wv/Or9vv2KobqNG/SQ+9Q==",
                "active": false,
                "pending": false,
                "deleted": 0,
                "expiryDate": "2019-06-10 13:30:29",
                "type": [
                    "sales"
                ],
                "cgCreationDate": "2017-07-05 09:22:16",
                "stockManagement": 0,
                "externalData": {
                    "importEbayEmails": 1,
                    "globalShippingProgram": 0,
                    "listingLocation": "Manchester",
                    "listingCurrency": null,
                    "paypalEmail": "accounts@channelgrabber.com",
                    "listingDuration": "GTC",
                    "listingDispatchTime": 1,
                    "listingPaymentMethods": [
                        "PayPal"
                    ],
                    "oAuthExpiryDate": "2020-08-16 01:02:32"
                },
                "displayChannel": null,
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": 1560132005,
                    "processed": 0,
                    "total": 0,
                    "lastCompletedDate": "2019-06-05 02:00:15"
                },
                "listingsAuthActive": false,
                "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=MzE3MA==",
                "siteId": 3
            },
            "3243": {
                "id": 3243,
                "externalId": null,
                "application": "OrderHub",
                "channel": "amazon",
                "organisationUnitId": 10949,
                "rootOrganisationUnitId": 10558,
                "displayName": "Amazon EU",
                "credentials": "7KcoJhcMl9cT5rTLVAL58CRe2hSr87TkfK9zdFb2urppxk7EPnDoisnizeXmEKMFeYlaBwVoYJG/J8rIB+MwSKJkisYeQ3Az/egy0uwOs3UE7Z/UunXuA5xMRIZLI0GkzHYH0DW/67U6/zrSNdNuJ+QXgQLiKIVp93xExHup3yGD0YLHvxNSw58/RoWrRLpL+TXpT6X9VHfigokGHrIu4uwRnyByUyQHaAyeUGBxItx/pbeldHE1T3LJoY8JiGkoQwIUYCiE4jqoLHCwXqzZ9pWQG5LFNiSaGkA7WmOmTsWZbymL7Mg/YA7DzyjuFXS1mfdDFCRN512M1fshjF+X4psCnutqb+k8UgBvEHGedBJtQrfecrFRmkovlWD0Po62JCzpJAGCKIQ6IZqioKZQLEhD4J5AMcmx+CiVHku52/5WvByALEWvk20Mu4nthBHMnrc8whDd1HNzL+Idj6r6PaebVX22E1mb8w7pUUcu20lA9LNwcBoT4VDHHXtO5nPwD4vsPAUykf5es2vuEHZfhXDN4kdJ2s0ZS85dqxK53Qc8IsI19Bifb0Z4ofOFhFApFJ+8Bn6/Dcm8KahJzH76F2cj2bSevXflDLtlbSzQjILyYP9UifJncZLriu6mHKv+b4oshBCzIl03MdWXgoLUrHhgOiGB1jctzTp92TYX0iQwomQDHudS56ZaGGDBMlJtED3F6ojBhNBZQy+vIeBTcbIBgs60H7PgAjbPACtt/nN4zlw/eOFQfUEyWW3g9lqGPaIGwZJFUZeFC1F+DmfyReyoOqOaHhe9HRYWxFVx/QDjTZReHTHtSFIsAVX7HH8lyH8XnJqVKqdIztJWmikcoyj4Djj/LtcOFlktZFRHtZZFZW9i8/gKWj5LuA0QhlwgteoF0cY8KNMqYhpdghRggN3mU66tiXl0hSHLbLsPSORRdJgi4esMT7jXzvFVXhllCqycx0EkrIbnz2HTndq9b6lAdDTxxLIEPcdObHnOAPLPWxlmrxzxxC6922DqkqlK9bmkKtrTHDuN1DrGP2p+Lf3mdrTGoskYwVJ2x89mR0QnnQtQM3jjXst/ujwZfRKtEOMNx1Cwq3uqOSsCTt434A==",
                "active": true,
                "pending": false,
                "deleted": 0,
                "expiryDate": null,
                "type": [
                    "sales",
                    "shipping"
                ],
                "cgCreationDate": "2017-07-11 10:47:32",
                "stockManagement": 0,
                "externalData": {
                    "fbaOrderImport": 0,
                    "hash": "3e474f1d119d973829736ed9f7e18a699ff607bf",
                    "originalEmailAddress": "",
                    "fulfillmentLatency": 2,
                    "mcfEnabled": 1,
                    "messagingSetUp": 0,
                    "includeFbaStock": 0,
                    "stockFromFbaLocationId": 2796,
                    "regionCode": null,
                    "marketplaceIds": "A13V1IB3VIYZZH,A1F83G8C2ARO7P,A1PA6795UKMFR9,A1RKKUPIHCS9HS,A1ZFFQZ3HTUKT9,A38D8NSA03LJTC,A62U237T8HV6N,AFQLKURYRPEL8,APJ6JRA9NG5V4,AZMDEXL2RVFNN"
                },
                "displayChannel": null,
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": 1563850808,
                    "processed": 49,
                    "total": 45,
                    "lastCompletedDate": "2019-07-23 03:36:47"
                }
            },
            "3250": {
                "id": 3250,
                "externalId": "2015291000",
                "application": "OrderHub",
                "channel": "royal-mail-nd",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "Royal Mail - NetDespatch",
                "credentials": "TPAJ661Zv8weflgLAfGnxOUuidrIqYz7h2VF+DXL62F97ORyRp43wkY66Xf6AidA8MPWZNmo3QiBHLP5T7/mOZ7v69O1wf07NGm/1G9TvAH8RmJXxSmk069W9MTANKOgEkaKQYpSEyRG23qfYHx5bHgg9gM9+ljgEbbfpwVJSIMo0Ve18dFSGz28O5y74k7tcmbHyFe5NWjR2imIEkvQ75Ph4Dw6Xj2yY3d5W6sc4EjiRAJ7PH/01xkQFGuVkbFIARVjk8qeMnY9qOjuqrWoRUrJEpldvcuTj6VSwjtsImEDc7S8vcBuqtuLHQACUqi0em4OSOsEUa6Uty7rodNGhArJLkhmHX5KMX+tjc+tqunrHgTk1PW4OP0gqJx3PqFKnS/DWlQ1DzNe4/OhBzwQF2+zi1ovdVJCtj5Bt+L1fpYGad82rwHa2j6mTTnQXPNGaa3uBpXKDALBz2/s/XZNIXElriH1/h+UCMupDfDiCiDJ2SeHU8J9HnraiYswLVmlUqL468lqoL/9ALeqXeLzIW0LM5lFz6df8cpZtTCV0Ew=",
                "active": false,
                "pending": false,
                "deleted": 0,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2017-07-11 14:38:55",
                "stockManagement": 0,
                "externalData": {
                    "accountType": "both",
                    "formSubmissionDate": "2017-07-11 14:38:57",
                    "domesticServices": "",
                    "internationalServices": ""
                },
                "displayChannel": null,
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            },
            "3252": {
                "id": 3252,
                "externalId": "3252",
                "application": "OrderHub",
                "channel": "myhermes-ca",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "MyHermes",
                "credentials": "7f0kv+R/TAgt0FTTQa/jAgjjqKeSJJEqscqyXH7V3jN1DE4phocq1MLfFFYGJ0a7AqSagcRmtARNBXvSRlw3nzboLdHgfTKdaOybiEmDmID2zI1cmpNdhi3h1wPelhCWOAkoPGPSCXyndPc0AzVDWHzRte2v76B5WJM7+QuVKgxxELxEMjub5BlN/WbQhjho/rCSTfQPW5Dahflawhb8eRPGKgFq0IdymRAikIXylt0ofznpXkIgxdiqvg9duxViHxmPQ41643IDrsosKt41Bm66fYg4e2WlU00l9ryf7upXbOlhKTFpvHDEDBy/GpDIMp2uHWf10fz1QzOjxdK5YIGwJF2mO9br6rFoN/Y4AVc=",
                "active": true,
                "pending": false,
                "deleted": 0,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2017-07-11 14:43:26",
                "stockManagement": 0,
                "externalData": {
                    "config": null
                },
                "displayChannel": "MyHermes",
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            },
            "3336": {
                "id": 3336,
                "externalId": "3336",
                "application": "OrderHub",
                "channel": "dpd-ca",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "DPD",
                "credentials": "gQR0bS/8kMl1ZdPqxuByLRgIzhL8ifWeqzS9R90xxZP/0lQR5ZhTPJHxLRD7FfS8PLepaPkA34Oix82txMxXWBzj2oWxO58d6+NV8s7QjYwo2Nt4TcljqCBUa2q9ci2bXAAEaFr1Rmnn/Q/DfLyS0NWbPbDjknBP0+//MK0BHZaHU4sQlMTe621Bor8up9S4jQZpUJpc7uksyCJwxG8LlhzNLlOIB7bov+KWx4zfUtKs93uGnGlfoUXylNVFCCwqJBJkyB++HMZIY9HPslnQ4doB8U8zwTV0zcu3hUdCWahbeEPSR8/zIOQn9GyOftzEWqa/3qB6VwLkg1DbtVU8DyCIbhcrzLaz9sOkl9XnMid+ZT5Gp3+w0auL7svxqiKSjmDvG81uifFbJxZL23Xk8EBpl9Sfy2/kTwpVlOlB4sy0Mm2zN1HeiJXb54tPBt7plNPYFWtxF83Ij53uL+cDpPzot8KqlK4DPQ92Yr6xRqoAqY1Vsw5okylz2k48Rw8q7sipDQYuza+A5v8LMjGVQzpl/gl8rh/WtcNxvyL+D4vSWafxx7GfIglUfschK9EZgxp1pC26UWBt7B41zQQIaSqKRmiCKkN8ZxrbQ5TyHUEY8LlZwJBwGxEye2qW6mL2",
                "active": true,
                "pending": false,
                "deleted": 0,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2017-07-21 14:20:27",
                "stockManagement": 0,
                "externalData": {
                    "config": null
                },
                "displayChannel": "DPD",
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            },
            "3337": {
                "id": 3337,
                "externalId": "3337",
                "application": "OrderHub",
                "channel": "interlink-ca",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "DPD Local",
                "credentials": "VsgnLNrzpX0lbxDf762CWR/zHY4J57fFZIPPMuHBWSs7q1woeJfGIhJNV3Q8aJufgNlA6g9ipRofQjMMT1GcGEcus5TQYzLrMag3PekIzx/LeW9HxYeJlS6+mVJ6pNki09+ePPLJfUhkZ0DgTghmh0Vk2/0Qst1ur9IvRmdjNoqDtCF9avYJlq3q2nxxf8erS1x6QwMe6h5BM4vOwJc44bt92/ioK6q+KaUJYrbEKDTv+X9skJuACyDVkrdC2o3KquD3ie2hKWfQ1BeXhlfId1WmO0KXFKZpn0bVAIsYPadjlubUmIxBw0IZ2vLMkkVQeJnmIIdmQ1ZDpD7YvZWNN/wYzHavIBa6+0UEeCfr1AlSw0bhg/F/ZWn4j77njvalqMbDQjFaiTALO2NofS3htvF4RQZVwEYXEKwtYZyaiRKotIY1a2tYAWOQmVJOluTPZfwD3WTlvB26xfE6ENiK7sgLiE6/dbrN4xTjAsTAB6d2Z0Hxm1Oa0xaUIy0/Pyg9oUUCkwzAlyP/dpXbtMUQiLbJxJg9uVMLw44lBf8WXVz5KcJK1/Hw8ZDfPWMz+dIj0uInuaA0kVyxz/xx8IUKSf21a03DibXKRGKDM0WSlhAO0+1gSqPQPLkw+srgAg0sRmzIXBk5rpq8opXd1QLv4ugYvkGs7rh+OU7mcl0rNIHIT+m8Hpn3SDeW66PYhnxqtmX2PAJ7K+jDQaKxmCguEA==",
                "active": true,
                "pending": false,
                "deleted": 0,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2017-07-21 14:42:40",
                "stockManagement": 0,
                "externalData": {
                    "config": null
                },
                "displayChannel": "DPD Local",
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            },
            "3747": {
                "id": 3747,
                "externalId": null,
                "application": "OrderHub",
                "channel": "royal-mail",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "Royal Mail (PPI)",
                "credentials": "Royal Mail",
                "active": false,
                "pending": false,
                "deleted": 0,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2017-08-24 15:38:05",
                "stockManagement": 0,
                "externalData": {
                    "PPINumber": ""
                },
                "displayChannel": null,
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            },
            "11660": {
                "id": 11660,
                "externalId": null,
                "application": "OrderHub",
                "channel": "shopify",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "Shopify",
                "credentials": "2nfg+u+z7qgiHaUqL7yEp5wBIVm2mDW9bW8IRa/tbJ/NVwBsRfGfZD3QNc4CHhkjidsA6bUMGlGTIcVhdvsB+yEecd65eRhg82xhJ6Phmwg51zsVENmCtRvuQ2tjJGpibW3M8gGAW4IJ+5eAdJbvG9jT9+OqlLLGVK4FSZ9+iQoHjKsQ6DqoQd892BOl7dFkcKLmSbKEAXQQXkRU0D9sMbecSmACoa0CSfBCGTEqgOE=",
                "active": false,
                "pending": false,
                "deleted": 0,
                "expiryDate": "2019-06-26 12:25:37",
                "type": [
                    "sales"
                ],
                "cgCreationDate": "2018-01-04 16:40:43",
                "stockManagement": 0,
                "externalData": {
                    "shopHost": "dev-shopify-orderhub-io.myshopify.com"
                },
                "displayChannel": null,
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": 1529671637,
                    "processed": 1,
                    "total": 1,
                    "lastCompletedDate": "2018-06-23 12:47:32"
                }
            },
            "12354": {
                "id": 12354,
                "externalId": "47fwg8cpdt",
                "application": "OrderHub",
                "channel": "big-commerce",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "BigCommerce",
                "credentials": "nYg4OGzKeMmDeVdlbKJRBg3WLTlxIWOK0or+WcNloytYBe91o1iu4DDfhTuCiat72+b+3G8yCA32SCXdB2+mzBxnW2mkSfUq73gHOKhQpNkBiA5dT+XYfHokcdHJErxWps+QtofTX0zFmINbvZOvmAvPpLdUblNMW6DI7IJ2wHoGFlo0UjB4b1PIZJ3HbgDZRD3RQWCIHbXgxpuOAKAJx6dHVtnx6hxqVGMrXB2CApU=",
                "active": true,
                "pending": false,
                "deleted": 0,
                "expiryDate": "2018-07-30 15:12:37",
                "type": [
                    "sales"
                ],
                "cgCreationDate": "2018-02-19 11:20:51",
                "stockManagement": 0,
                "externalData": {
                    "secureUrl": "https://store-47fwg8cpdt.mybigcommerce.com",
                    "weightUnits": "kg",
                    "dimensionUnits": "Centimeters"
                },
                "displayChannel": null,
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": 1521127671,
                    "processed": 13,
                    "total": 13,
                    "lastCompletedDate": "2018-03-15 15:28:15"
                }
            },
            "12355": {
                "id": 12355,
                "externalId": null,
                "application": "OrderHub",
                "channel": "ekm",
                "organisationUnitId": 10949,
                "rootOrganisationUnitId": 10558,
                "displayName": "Acme",
                "credentials": "Xu6vYvSef2w9DAA3fiZvhLYknLkLPyN9lGehw++yU23TAPfkgFESdOvWztrilx4/twu7etv1q/3xrxpQ2ZqZ6lE9MqsxovvQCRYZRTD2anY08cuzlDNJ5Xm7cds/SPcuA0bLkbkeO2SFgVqrc7cF4fJYfO/FLQOY878LYaTvFJL9xT8jx93gzf8TGDctB1IABpkLG3kaZ/7t1gD5adBukAbhzu9CA46r0YyqU4rDqFTGDS2BVp1z/p31ZFTElA42nRsHJdoJ+Q/ICfjLfD+NlELsRWne1dp4Y0x7FzZ6djfcS/ZvtWoPexv7Xz0VcGdz4Bz9odLqI50TOFJ+GPVOeE/XX9k8Hk9Yx2P/j1R082HZpK/NZlKdCD8ovh/g0oO4dSaZNYpKoZs3uUWogN56GvpQlxUf1CtorODaTTwBPPbSpSAEzzbtMfzJJSdbot6lM/hOyurtuwEAmao+V4jZwX7Pqq5DqrTXVZcHTtSUZQ2BXOP6W0ug07rcnXAXAd9dNcmld5d6ywHFhO1U3osZYrbat5niVRORBdmAu3842KhfuHefTnZ9D34H2YpqcR3wVsEd44oPgSY3EJU9n/lvUj/Aohn0Oz+uoOXlXPCiNBl1wQQ61CZSRsQqCF4tWgDdQsUb0wEJJaUSJ/JcnFBiZyEeTk5TnghbxhXSpSvuDcBNgh2cRUhal0mf/8+zRY8rEymTDzRxXoUd9InS3YJS64dfwJvJ4OOmq0bI1BXlMXs3IX/XAzS/Cyj8oAcw3eIx",
                "active": false,
                "pending": false,
                "deleted": 0,
                "expiryDate": "2019-05-31 22:01:11",
                "type": [
                    "sales"
                ],
                "cgCreationDate": "2018-02-19 11:37:54",
                "stockManagement": 0,
                "externalData": {
                    "ekmUsername": "channelgrabber"
                },
                "displayChannel": null,
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": 1560910676,
                    "processed": 392,
                    "total": 392,
                    "lastCompletedDate": "2019-06-19 04:34:37"
                }
            },
            "12628": {
                "id": 12628,
                "externalId": null,
                "application": "OrderHub",
                "channel": "woo-commerce",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "WooCommerce",
                "credentials": "n74zEWIEcNi2aN1IiseeY4EBTnZxbT+pIt/V6XmdNHJSxK/0SPaiiSWKFbV0qSJtk88YwMaTf2wIgN+32hsCPIertpodyhhccXY1D5C72z07zEe5R48fxaRtPOcZDdbcwQzKbdz6qMta9o5ZyAlsVqYmezGrQ+tugX0sx4wACg1caskGqzGSjsrXMsSzTeG5/S7B1kT9qhXXE7vGBks03Q3l1RK2lbdd1ilO1WorAQZVtZuCugBuPuINcADQ7RhkqloG7UALR5QuF3oTdofh5ZrqKwx8c0FCQErZKn5El9iWO7NvgaHybiizYrIPDUoAacRxpJXx8Z4BjlSrItmwlIMC1XPr/jzOh9CVU/9i0Vo9BkoOpHGXP0ykzP2fHdw1hRaV3UbnEe7QnR5Oqf1t5wdfCNOuVEG3cNTJqSY87l+6XBtN0918lq1vT8p2A5n56GuKMRsuzrb5afgNAfDmXA==",
                "active": false,
                "pending": false,
                "deleted": 0,
                "expiryDate": null,
                "type": [
                    "sales"
                ],
                "cgCreationDate": "2018-03-09 14:20:55",
                "stockManagement": 0,
                "externalData": {
                    "decimalSeparator": ".",
                    "thousandSeparator": ",",
                    "dimensionUnit": "cm",
                    "weightUnit": "kg",
                    "taxIncluded": 0,
                    "currency": "GBP",
                    "sslEnabled": 0,
                    "sslUsed": 0
                },
                "displayChannel": null,
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": 1555459231,
                    "processed": 92,
                    "total": 92,
                    "lastCompletedDate": "2019-04-17 00:02:27"
                }
            },
            "12917": {
                "id": 12917,
                "externalId": null,
                "application": "OrderHub",
                "channel": "royal-mail-click-drop",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "Royal Mail Click & Drop",
                "credentials": "FyGQTsTo6FxxEgfZP5u/mg3S1GYllTb1Fy7Rs6Va50hJKMOPelFoKHpygFXmXHysCbIR9GpAjIdCpopxwHuwvFAe5o1azYz+WlSKG8VGPJuDDFPhZ2dFPlW2s8DScHpjFO2TnH2D+7DmauR1W/Ttm2v8FIoWY2Go7+S+GE3fq1wZmf3eESt84Dn8hsx39lzz",
                "active": false,
                "pending": false,
                "deleted": 0,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2018-04-03 13:25:10",
                "stockManagement": 0,
                "externalData": [],
                "displayChannel": null,
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            },
            "14098": {
                "id": 14098,
                "externalId": "14098",
                "application": "OrderHub",
                "channel": "dpd-ca",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "DPD",
                "credentials": "uUXAEpiPi895LUMm4TvfhhQDFNZUt6mMfjkRyqEKtBODeuPnx2f5EmQUhp8AQmYbMbyM6B7amdpHW0alXAG7gt/ufw1Ndhb+iK/q8if8V6pNK49HGba7f1jY5/0d9+QEN0TAeadR0eSZeawW1BweyvpGx1b6sLF21ejgecRKHPtFPniv/Ym6EO26D9OSyyZSIZygyintBBX7r9fnCiCA2BRk/IR49CrdYTz5jeEubd8ARsY7MJXZjE6O6/TePqYKzXio4Q+GnA2i4Tc+dt9WbfaiRHQjpT5daot6wDEWeIDtm2fe4w+f44uuitY9S5zSVdcMFO1Piu7LPk6ohoebtoK4OQUZpQVJDMNlO8vgX4zbgT1GxYzDx1FdVtniKnp6eZbt3rp+2h3WNbN27w3NoMWQa9Lo9SHaz6zHgnhTveTFQ/oW142T1n3wEzE4qKAurT2hOix61b2uUO0wP9R95b2v0ryLxLRjmI2KX2pp08UBa9VAgAh22fI1KhK38LVigCE+0doPUcEnF0FeogihMdtC9ZFRP13az2+sfw/p+STwbCpujEdfg4qpDLRAkE0saEUVBBpzSA4ipunEm++PrsqDv2jXlMGeP+ViUD+m/BcW5von+M/d3q9JsjT7hzak",
                "active": false,
                "pending": false,
                "deleted": 0,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2018-08-23 13:00:52",
                "stockManagement": 0,
                "externalData": {
                    "config": null
                },
                "displayChannel": "DPD",
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            },
            "15504": {
                "id": 15504,
                "externalId": "15504",
                "application": "OrderHub",
                "channel": "royal-mail-intersoft-ca",
                "organisationUnitId": 10558,
                "rootOrganisationUnitId": 10558,
                "displayName": "Royal Mail OBA",
                "credentials": "cbwMXR2z3oeu91Jcqi8PbLZwsbe6mRSmKotJNhSP740WFyH/xN+I8AzruE/RgDS0urTeMdRLX/EaaeVaVszk/SnJVyih4VJyOmskqrzzGVrA5iaPGM1nLcY4rwzZZd9DFNLYqEoSbqgJ141orsmCLVL2ApOLY7SpfePZywCmFnKlTJ3FDsSLix4kX0wjuIWi3QJYAMFahOrb6RXR24BVijZ/x9mniiKUBkzJZccDuvGcpRLcNWgnl//d/Hspnum7fmCjhcVZpeKcTjyCPlNKyrbwqtYYCyiADvh4zzQt1rFPR4DlSE6z87M85yPTiAr/1UWTohXOrE/vOPSZmxw5cOg6Y6weDvi2GDtu4CZvR2+Q3k89NyyAIT/VLGMugWmJKVf+aibk6dpd5E4URa23CA==",
                "active": true,
                "pending": false,
                "deleted": 0,
                "expiryDate": null,
                "type": [
                    "shipping"
                ],
                "cgCreationDate": "2019-04-18 09:29:14",
                "stockManagement": 0,
                "externalData": {
                    "config": null
                },
                "displayChannel": "Royal Mail OBA (In)",
                "orderNotificationUrl": "",
                "stockNotificationUrl": "",
                "stockMaximumEnabled": false,
                "stockFixedEnabled": false,
                "autoImportListings": false,
                "listingDownload": {
                    "id": null,
                    "processed": null,
                    "total": null,
                    "lastCompletedDate": null
                }
            }
        },
        "stockModeDefault": "all",
        "stockLevelDefault": null,
        "lowStockThresholdDefault": {
            "toggle": true,
            "value": 5
        },
        "taxRates": {
            "GB": {
                "GB1": {
                    "name": "Standard",
                    "rate": 20
                },
                "GB2": {
                    "name": "Reduced",
                    "rate": 5
                },
                "GB3": {
                    "name": "Zero",
                    "rate": 0,
                    "selected": true
                }
            }
        },
        "variationCount": 3,
        "variationIds": [
            "11400132",
            "11400134",
            "11409247"
        ]
    },
    "attributeNames": [
        "Colour"
    ],
    "initialDimensions": {
        "11400132": {
            "length": 0,
            "width": 0,
            "height": 0,
            "weight": 0
        },
        "11400134": {
            "length": 0,
            "width": 0,
            "height": 0,
            "weight": 0
        },
        "11409247": {
            "length": 0,
            "width": 0,
            "height": 0,
            "weight": 0
        }
    },
    "accounts": [
        {
            "id": 3243,
            "externalId": null,
            "application": "OrderHub",
            "channel": "amazon",
            "organisationUnitId": 10949,
            "rootOrganisationUnitId": 10558,
            "displayName": "Amazon EU",
            "credentials": "7KcoJhcMl9cT5rTLVAL58CRe2hSr87TkfK9zdFb2urppxk7EPnDoisnizeXmEKMFeYlaBwVoYJG/J8rIB+MwSKJkisYeQ3Az/egy0uwOs3UE7Z/UunXuA5xMRIZLI0GkzHYH0DW/67U6/zrSNdNuJ+QXgQLiKIVp93xExHup3yGD0YLHvxNSw58/RoWrRLpL+TXpT6X9VHfigokGHrIu4uwRnyByUyQHaAyeUGBxItx/pbeldHE1T3LJoY8JiGkoQwIUYCiE4jqoLHCwXqzZ9pWQG5LFNiSaGkA7WmOmTsWZbymL7Mg/YA7DzyjuFXS1mfdDFCRN512M1fshjF+X4psCnutqb+k8UgBvEHGedBJtQrfecrFRmkovlWD0Po62JCzpJAGCKIQ6IZqioKZQLEhD4J5AMcmx+CiVHku52/5WvByALEWvk20Mu4nthBHMnrc8whDd1HNzL+Idj6r6PaebVX22E1mb8w7pUUcu20lA9LNwcBoT4VDHHXtO5nPwD4vsPAUykf5es2vuEHZfhXDN4kdJ2s0ZS85dqxK53Qc8IsI19Bifb0Z4ofOFhFApFJ+8Bn6/Dcm8KahJzH76F2cj2bSevXflDLtlbSzQjILyYP9UifJncZLriu6mHKv+b4oshBCzIl03MdWXgoLUrHhgOiGB1jctzTp92TYX0iQwomQDHudS56ZaGGDBMlJtED3F6ojBhNBZQy+vIeBTcbIBgs60H7PgAjbPACtt/nN4zlw/eOFQfUEyWW3g9lqGPaIGwZJFUZeFC1F+DmfyReyoOqOaHhe9HRYWxFVx/QDjTZReHTHtSFIsAVX7HH8lyH8XnJqVKqdIztJWmikcoyj4Djj/LtcOFlktZFRHtZZFZW9i8/gKWj5LuA0QhlwgteoF0cY8KNMqYhpdghRggN3mU66tiXl0hSHLbLsPSORRdJgi4esMT7jXzvFVXhllCqycx0EkrIbnz2HTndq9b6lAdDTxxLIEPcdObHnOAPLPWxlmrxzxxC6922DqkqlK9bmkKtrTHDuN1DrGP2p+Lf3mdrTGoskYwVJ2x89mR0QnnQtQM3jjXst/ujwZfRKtEOMNx1Cwq3uqOSsCTt434A==",
            "active": true,
            "pending": false,
            "deleted": 0,
            "expiryDate": null,
            "type": [
                "sales",
                "shipping"
            ],
            "cgCreationDate": "2017-07-11 10:47:32",
            "stockManagement": 0,
            "externalData": {
                "fbaOrderImport": 0,
                "hash": "3e474f1d119d973829736ed9f7e18a699ff607bf",
                "originalEmailAddress": "",
                "fulfillmentLatency": 2,
                "mcfEnabled": 1,
                "messagingSetUp": 0,
                "includeFbaStock": 0,
                "stockFromFbaLocationId": 2796,
                "regionCode": null,
                "marketplaceIds": "A13V1IB3VIYZZH,A1F83G8C2ARO7P,A1PA6795UKMFR9,A1RKKUPIHCS9HS,A1ZFFQZ3HTUKT9,A38D8NSA03LJTC,A62U237T8HV6N,AFQLKURYRPEL8,APJ6JRA9NG5V4,AZMDEXL2RVFNN"
            },
            "displayChannel": null,
            "orderNotificationUrl": "",
            "stockNotificationUrl": "",
            "stockMaximumEnabled": false,
            "stockFixedEnabled": false,
            "autoImportListings": false,
            "listingDownload": {
                "id": 1563850808,
                "processed": 49,
                "total": 45,
                "lastCompletedDate": "2019-07-23 03:36:47"
            }
        }
    ],
    "massUnit": "kg",
    "lengthUnit": "cm",
    "variationImages": {
        "EXRED": {
            "imageId": 13812565
        },
        "EXBLU": {
            "imageId": 13812565
        },
        "EXWHI": {
            "imageId": 13812565
        }
    },
    "images": true,
    "attributeNameMap": {}
};


const DimensionsSection = React.memo(props => {
    console.log('rendering DIMENSIONSSECTION');
    return (
        <span>
            <span className="heading-large heading-table">Dimensions</span>
            <Dimensions
                {...props}
            />
        </span>
    );
});



// dimensionsState is passed by reference to DimensionsHOC
let dimensionsState = {};
function submitForm (values, dispatch, props){
    dispatch(Actions.submitListingsForm(
        dispatch,
        values,
        props,
        dimensionsState
    ));
}

// jigsawing React based forms in after deciding to migrate from redux-form
const DimensionsHOC = (props) => {
    let {children} = props;
    dimensionsState.stateValue = useState();
    return children({
        ...props
    });
};

const DimensionsComponent = (props) => {
    console.log('in DimensionsComponent ', props);
    return <div>DimensionsComponent</div>
};

class CreateListingPopup extends React.Component {
    static defaultProps = {
        product: {},
        accounts: [],
        categories: [],
        conditionOptions: [],
        categoryTemplates: {},
        variationsDataForProduct: {},
        initialDimensions: {},
        accountsData: {},
        initialProductPrices: {},
        defaultCurrency: null,
        accountDefaultSettings: {},
        submissionStatuses: {},
        onCreateListingClose: function() {},
        massUnit: null,
        lengthUnit: null,
        selectedProductDetails: {},
        productSearchActive: false,
        productSearch: {},
        defaultProductImage: ''
    };

    componentDidMount() {
        this.props.fetchCategoryTemplateDependentFieldValues();
        this.props.loadInitialValues(this.findSearchAccountId());
    }

    componentWillUnmount() {
        this.props.revertToInitialValues();
    }

    componentDidUpdate() {
        if (this.isPbseRequired() && this.areAllVariationsAssigned()) {
            this.props.clearErrorFromProductSearch();
        }
    }

    renderForm = () => {
        console.log('dimensionsState going into render form', dimensionsState);


        return <form>
            <DimensionsHOC
                dimensionsState={dimensionsState}
                outsideIn={'outsideProp'}
            >
                {(props) => {
                    return <DimensionsComponent
                        {...props}
                    />
                }}
            </DimensionsHOC>
        </form>

//        return <form>
//            {this.renderProductIdentifiers()}
//            {this.renderDimensions()}
//        </form>

//        return <form>
//            <Field name="title" component={this.renderInputComponent} displayTitle={"Listing Title:"}/>
//            <Field name="description" component={this.renderTextAreaComponent} displayTitle={"Description:"}/>
//            <Field name="brand" component={this.renderInputComponent} displayTitle={"Brand (if applicable):"}/>
//            <Field name="condition" component={this.renderSelectComponent} displayTitle={"Item Condition:"} options={this.props.conditionOptions} validate={Validators.required} />
//            <Field name="imageId" component={this.renderImagePickerField} validate={Validators.required} />
//            {this.renderChannelFormInputs()}
//            {this.renderCategoryFormInputs()}
//            {this.renderProductIdentifiers()}
//            {this.renderDimensions()}
//            {this.renderProductPrices()}
//        </form>
    };

    findSearchAccountId = () => {
        let accountId = this.props.accounts.find(function(accountId) {
            let accountData = this.props.accountsData[accountId];
            return accountData.channel == 'ebay' && accountData.listingsAuthActive;
        }, this);

        return accountId > 0 ? accountId : null;
    };

    renderProductSearchComponent = () => {
        if (!this.shouldRenderProductSearchComponent()) {
            return null;
        }

        return <ProductSearch
            accountId={this.props.searchAccountId}
            mainProduct={this.props.product}
            variationsDataForProduct={this.props.variationsDataForProduct}
            clearSelectedProduct={this.props.clearSelectedProduct}
            variationImages={this.props.variationImages}
            defaultProductImage={this.props.defaultProductImage}
        />;
    };

    shouldRenderProductSearchComponent = () => {
        if (!this.props.productSearchActive) {
            return false;
        }

        return !!this.props.searchAccountId;
    };

    renderInputComponent = (field) => {
        return <label className="input-container">
            <span className={"inputbox-label"}>{field.displayTitle}</span>
            <div className={"order-inputbox-holder"}>
                <Input
                    name={field.input.name}
                    value={field.input.value}
                    onChange={this.onInputChange.bind(this, field.input)}
                    className={Validators.shouldShowError(field) ? 'error' : null}
                />
            </div>
            {Validators.shouldShowError(field) && (
                <span className="input-error">{field.meta.error}</span>
            )}
        </label>;
    };

    renderTextAreaComponent = (field) => {
        return <label className="input-container">
            <span className={"inputbox-label"}>{field.displayTitle}</span>
            <div className={"order-inputbox-holder"}>
                <TextArea
                    name={field.input.name}
                    value={field.input.value}
                    onChange={this.onInputChange.bind(this, field.input)}
                    className={"textarea-description " + (Validators.shouldShowError(field) ? 'error' : '')}
                />
            </div>
            {Validators.shouldShowError(field) && (
                <span className="input-error">{field.meta.error}</span>
            )}
        </label>;
    };

    renderSelectComponent = (field) => {
        return <label className="input-container">
            <span className={"inputbox-label"}>{field.displayTitle}</span>
            <div className={"order-inputbox-holder"}>
                <Select
                    autoSelectFirst={false}
                    onOptionChange={this.onSelectOptionChange.bind(this, field.input)}
                    options={field.options}
                    selectedOption={this.findSelectedOption(field.input.value, field.options)}
                    className={Validators.shouldShowError(field) ? 'error' : null}
                    classNames={'u-width-300px'}
                />
            </div>
            {Validators.shouldShowError(field) && (
                <span className="input-error">{field.meta.error}</span>
            )}
        </label>;
    };

    findSelectedOption = (value, options) => {
        var selectedOption = {
            name: '',
            value: ''
        };
        options.forEach(function(option) {
            if (option.value == value) {
                selectedOption = option;
            }
        });
        return selectedOption;
    };

    onSelectOptionChange = (input, option) => {
        this.onInputChange(input, option.value);
    };

    onInputChange = (input, value) => {
        input.onChange(value);
    };

    renderImagePickerField = (field) => {
        return (<label className="input-container">
            <span className={"inputbox-label"}>Images:</span>
            {this.renderImagePicker(field)}
            {Validators.shouldShowError(field) && (
                <span className="input-error">{field.meta.error}</span>
            )}
        </label>);
    };

    renderImagePicker = (field) => {
        if (this.props.product.images.length == 0) {
            return (
                <p className="react-image-picker main-image-picker">No images available</p>
            );
        }
        return (
            <ImagePicker
                name={field.input.name}
                multiSelect={false}
                images={this.props.product.images}
                onImageSelected={this.onImageSelected.bind(this, field.input)}
                className={Validators.shouldShowError(field) ? 'error' : null}
            />
        );
    };

    onImageSelected = (input, selectedImage, selectedImageIds) => {
        input.onChange(selectedImageIds);
        input.onBlur(selectedImageIds);
    };

    renderChannelFormInputs = () => {
        return <FormSection
            name="channel"
            component={ChannelForms}
            accounts={this.props.accounts}
            categoryTemplates={this.props.categoryTemplates.categories}
            product={this.props.product}
            variationsDataForProduct={this.props.variationsDataForProduct}
            currency={this.props.defaultCurrency}
        />;
    };

    renderCategoryFormInputs = () => {
        return <FormSection
            name="category"
            component={CategoryForms}
            accounts={this.props.accounts}
            categoryTemplates={this.props.categoryTemplates.categories}
            product={this.props.product}
            variationsDataForProduct={this.props.variationsDataForProduct}
            fieldChange={this.props.change}
            resetSection={this.props.resetSection}
            selectedProductDetails={this.props.selectedProductDetails}
        />;
    };

    renderProductIdentifiers = () => {
        //////
        let props = {
            "variationsDataForProduct": [
                {
                    "id": 11400132,
                    "organisationUnitId": 10558,
                    "sku": "EXRED",
                    "name": "",
                    "deleted": false,
                    "parentProductId": 11400129,
                    "attributeNames": [],
                    "attributeValues": {
                        "Colour": "Red"
                    },
                    "imageIds": [
                        {
                            "id": 13812565,
                            "order": 0
                        }
                    ],
                    "listingImageIds": [
                        {
                            "id": 13812565,
                            "listingId": 10222599,
                            "order": 0
                        }
                    ],
                    "taxRateIds": [],
                    "cgCreationDate": "2019-05-03 09:28:10",
                    "pickingLocations": [],
                    "eTag": "2a4512ac55866b638c6d9749dacfa5f720e496d0",
                    "images": [
                        {
                            "id": 13812565,
                            "organisationUnitId": 10558,
                            "url": "https://channelgrabber.23.ekm.shop/ekmps/shops/channelgrabber/images/excalibur-stone-not-supplied-103-p.jpeg"
                        }
                    ],
                    "listings": {
                        "10222599": {
                            "id": 10222599,
                            "organisationUnitId": 10558,
                            "productIds": [
                                11400129,
                                11400132,
                                11400134,
                                11409247
                            ],
                            "externalId": "103",
                            "channel": "ekm",
                            "status": "active",
                            "name": "Excalibur (stone not supplied)",
                            "description": "Wielded by King Arthur!*<br /><br /><br /><br />* we think",
                            "price": "2.0000",
                            "cost": null,
                            "condition": "New",
                            "accountId": 3086,
                            "marketplace": "",
                            "productSkus": {
                                "11400129": "",
                                "11400132": "EXRED",
                                "11400134": "EXBLU",
                                "11409247": "EXWHI"
                            },
                            "replacedById": null,
                            "skuExternalIdMap": [],
                            "lastModified": null,
                            "url": "https://23.ekm.net/ekmps/shops/channelgrabber/index.asp?function=DISPLAYPRODUCT&productid=103",
                            "message": ""
                        }
                    },
                    "listingsPerAccount": {
                        "3086": [
                            10222599
                        ]
                    },
                    "activeSalesAccounts": {
                        "3243": {
                            "id": 3243,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "amazon",
                            "organisationUnitId": 10949,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Amazon EU",
                            "credentials": "7KcoJhcMl9cT5rTLVAL58CRe2hSr87TkfK9zdFb2urppxk7EPnDoisnizeXmEKMFeYlaBwVoYJG/J8rIB+MwSKJkisYeQ3Az/egy0uwOs3UE7Z/UunXuA5xMRIZLI0GkzHYH0DW/67U6/zrSNdNuJ+QXgQLiKIVp93xExHup3yGD0YLHvxNSw58/RoWrRLpL+TXpT6X9VHfigokGHrIu4uwRnyByUyQHaAyeUGBxItx/pbeldHE1T3LJoY8JiGkoQwIUYCiE4jqoLHCwXqzZ9pWQG5LFNiSaGkA7WmOmTsWZbymL7Mg/YA7DzyjuFXS1mfdDFCRN512M1fshjF+X4psCnutqb+k8UgBvEHGedBJtQrfecrFRmkovlWD0Po62JCzpJAGCKIQ6IZqioKZQLEhD4J5AMcmx+CiVHku52/5WvByALEWvk20Mu4nthBHMnrc8whDd1HNzL+Idj6r6PaebVX22E1mb8w7pUUcu20lA9LNwcBoT4VDHHXtO5nPwD4vsPAUykf5es2vuEHZfhXDN4kdJ2s0ZS85dqxK53Qc8IsI19Bifb0Z4ofOFhFApFJ+8Bn6/Dcm8KahJzH76F2cj2bSevXflDLtlbSzQjILyYP9UifJncZLriu6mHKv+b4oshBCzIl03MdWXgoLUrHhgOiGB1jctzTp92TYX0iQwomQDHudS56ZaGGDBMlJtED3F6ojBhNBZQy+vIeBTcbIBgs60H7PgAjbPACtt/nN4zlw/eOFQfUEyWW3g9lqGPaIGwZJFUZeFC1F+DmfyReyoOqOaHhe9HRYWxFVx/QDjTZReHTHtSFIsAVX7HH8lyH8XnJqVKqdIztJWmikcoyj4Djj/LtcOFlktZFRHtZZFZW9i8/gKWj5LuA0QhlwgteoF0cY8KNMqYhpdghRggN3mU66tiXl0hSHLbLsPSORRdJgi4esMT7jXzvFVXhllCqycx0EkrIbnz2HTndq9b6lAdDTxxLIEPcdObHnOAPLPWxlmrxzxxC6922DqkqlK9bmkKtrTHDuN1DrGP2p+Lf3mdrTGoskYwVJ2x89mR0QnnQtQM3jjXst/ujwZfRKtEOMNx1Cwq3uqOSsCTt434A==",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "sales",
                                "shipping"
                            ],
                            "cgCreationDate": "2017-07-11 10:47:32",
                            "stockManagement": 0,
                            "externalData": {
                                "fbaOrderImport": 0,
                                "hash": "3e474f1d119d973829736ed9f7e18a699ff607bf",
                                "originalEmailAddress": "",
                                "fulfillmentLatency": 2,
                                "mcfEnabled": 1,
                                "messagingSetUp": 0,
                                "includeFbaStock": 0,
                                "stockFromFbaLocationId": 2796,
                                "regionCode": null,
                                "marketplaceIds": "A13V1IB3VIYZZH,A1F83G8C2ARO7P,A1PA6795UKMFR9,A1RKKUPIHCS9HS,A1ZFFQZ3HTUKT9,A38D8NSA03LJTC,A62U237T8HV6N,AFQLKURYRPEL8,APJ6JRA9NG5V4,AZMDEXL2RVFNN"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1563850808,
                                "processed": 49,
                                "total": 45,
                                "lastCompletedDate": "2019-07-23 03:36:47"
                            }
                        },
                        "12354": {
                            "id": 12354,
                            "externalId": "47fwg8cpdt",
                            "application": "OrderHub",
                            "channel": "big-commerce",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "BigCommerce",
                            "credentials": "nYg4OGzKeMmDeVdlbKJRBg3WLTlxIWOK0or+WcNloytYBe91o1iu4DDfhTuCiat72+b+3G8yCA32SCXdB2+mzBxnW2mkSfUq73gHOKhQpNkBiA5dT+XYfHokcdHJErxWps+QtofTX0zFmINbvZOvmAvPpLdUblNMW6DI7IJ2wHoGFlo0UjB4b1PIZJ3HbgDZRD3RQWCIHbXgxpuOAKAJx6dHVtnx6hxqVGMrXB2CApU=",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": "2018-07-30 15:12:37",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2018-02-19 11:20:51",
                            "stockManagement": 0,
                            "externalData": {
                                "secureUrl": "https://store-47fwg8cpdt.mybigcommerce.com",
                                "weightUnits": "kg",
                                "dimensionUnits": "Centimeters"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1521127671,
                                "processed": 13,
                                "total": 13,
                                "lastCompletedDate": "2018-03-15 15:28:15"
                            }
                        }
                    },
                    "accounts": {
                        "844": {
                            "id": 844,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "ebay",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Calico Trading",
                            "credentials": "iCpnvOwePsMJq7J40bqlO77erZ5X+00dzKvuRk4PpSGCEsEYCixvrALXTh8lZ4anCsePIJMLRFc8MN0C2DNL7WWBffE20qfU4ZmfY6BtJjLVpXn3Y8/aLas6uI7BYX+xydtAavkSmiGJOLPEtQZqCpHT008zPFTA59ebB4tDe2DvZHIZAPoxMX+QfpaaujyBxpzw3RGmId4C6LzUJ2G5meV8tzw92/SMU5alnWCrX+p1LUK3tk7CJRFDU6PSOn8Lh8ZegQEAoMUGMEOCZuIvhopDmYiCm2PLvk1f+IofZXTufQtAjZBS5yyDTVqqKSS056zp02tyh3J0aATDFpVONkJ3IaTFRNpH0eG3nwwsI0RgaRPTNVr/c2Nhf/KblTE0P8iOus8UJZTIesgXQApt2yvUr/P/X/VD0gkXZO/nREmdRqAerC1Usx5mCLvAUBYoNo3el8jsdYFX2ykzbwFd0cHJGaQPujEdjmR4ELs/llTelUGT6v+MIrfw9cZQ8SrP2OziAP5lsrr9tqi9xG45dGas+/jCOWdU8eAxm5rcQEtDlWG1Kk74tbwWFLqMgrKIVE+yx5Xtud+cKgEp2IDD+4bc/7plEJBW0XQ6nMJPelfKq4DnQe4vw0hcgyJjAzJFyDQtN0xVlOmciVHRi44PTgEFKTVUmwBzwvxsNeUR1an5qeZ67gOxRHgndI0QVq3aKI8vm8+1arW1Hg7iYYbdoZ0L+Inl+SGRdQFVwfvgmLjV9YacJV4o/m2X/RUawj7i386r1HSitafwnICDgsOk/psvSb7phj4Z/2jxx+E5VjlW38v6bVpk6UYuGimbVyh9gqNGq3oX1rRPG7jAiUQTGIoSFt56BJFAEyDMXqNnzv3c/nYm+gTw40pmhPPAUMb30ZMecHdIG5ulqgaQaxADOM3Lc4VddBhFO9ejdIrACf+Az+TI4dzGgDnh/62yrS6hkdP5qR3N1LUQmyEgDH386oy7uQsoe57Dnuy29YNI9ijjC/3Zlf0k/O0SzqFCKGDOTOWPDA8yj5bw4ZnwyWE0Sl7FF3QshrhdmMlJ8hZz6oE8M3J8ynNPNzHl4k+ItplWSp+tnOgMv8r6CZ3/jvW1vfUQ1z2kzw7g8dt6NcQjFTbCAriDdhJPYTgeOtDRwaWpHuFrezA0suhYNVo/7CUyGzkOk1XFpMazNlBUKDFHFwGAHLMRLTKivg0r/8pQzoqROxUjDedGs8YXQNRAkQzdQx2cTEwW4yJNrEn9j8nFD+84l5j+xKTQfwkbfQ0AzVBO/psTYA4PAZDArtxqxiTroiMNdaZ3P8vXDpojkardR2QKsQEEoInXaGHpNzxLVdnrZcbRBCZMaWacecUH6H7vE41PAnslbm6E/0h1gCHK2tqYCLH1M/iYTL/hp64nPlPyCb3P0/TGu/gFcamxSRqPF4cP/MnENAtgIW9UxRsEEUbMVSvYxg9MtkADggF9pmL2L4Crkj+FbTZ7+yhRxhU2ycwbhZzoEXDOqPauxnDEXIbXlV0gJrUnhwIcA0NQi5JkyZukM3HjvWX4j/MB1mFsKlA0wdfVYmh8kIFr6bLCfjuipbC/sUIB/93U+rvSGiaVNqM52w6dJjIQZ+p9eDJzKyHy5JIipPRhCcMpBx5xnUA9rlwhOhy9wKzxRfUQApXOPu2MavivSO/8cP5mLdkylbH3T1vBBcuSVcHhQ+Wvhpd4R1zIAt8EtZyfSJgsiw3EsQHXfebAoKffXQNKX63T2bXJi4WAOrRYjPAsey+YmHk=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": "2017-07-20 12:16:35",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2016-01-27 12:16:36",
                            "stockManagement": 0,
                            "externalData": {
                                "importEbayEmails": 0,
                                "globalShippingProgram": 0,
                                "listingLocation": null,
                                "listingCurrency": null,
                                "paypalEmail": null,
                                "listingDuration": null,
                                "listingDispatchTime": null,
                                "listingPaymentMethods": [],
                                "oAuthExpiryDate": null
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": true,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            },
                            "listingsAuthActive": false,
                            "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=ODQ0",
                            "siteId": 3
                        },
                        "1096": {
                            "id": 1096,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "royal-mail",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Royal Mail PPI",
                            "credentials": "Royal Mail",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-05-25 09:55:08",
                            "stockManagement": 0,
                            "externalData": {
                                "PPINumber": "HQ12345"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1445": {
                            "id": 1445,
                            "externalId": "1445",
                            "application": "OrderHub",
                            "channel": "parcelforce-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Parcelforce",
                            "credentials": "2+p/GVQs1ndg/heKwGT6bePmrr9ElapPzIhmSYdggFDPypxFY+/sIyYl5nWNhBpugPdB/rWFnyon41Trir9I1tPLadwkG3tx4nXqeN1Fs417/NKHRZtZw2pYcLAOYiJO5egBD/wtYAVOWwTie99HiBsOXxjuOifLQ3/eoo2lgorjmnQeRJ5sKY535YOsHS3m0F47C2ypo5emUIw3pXCoSncxdDydOmrY0H5tJLUIA9nGZ7DDuNBQyfFuu97XsIExuriMw3qIg9MXPcAFy56silpxXdE8qMAlIN9NNJQqlcSOt++u6XpoeO6FEHXmvc/186H3Pi/XXwp/xpr7+0Y8FK6K0/rPga17hGWRLY+AidVnNyYl7qc1LljcEmhSXD58fpzMIOcH6XRjiV/giHHZ4EqTKBMIBpxwJ8fpqpJAGAlGs7t05vol/44LQ37cVzNp",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-08-30 14:27:11",
                            "stockManagement": 0,
                            "externalData": {
                                "config": "{\"emailNotification\":\"1\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"1\",\"sms_pre_delivery\":\"0\"}"
                            },
                            "displayChannel": "Parcelforce",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1447": {
                            "id": 1447,
                            "externalId": "1447",
                            "application": "OrderHub",
                            "channel": "dpd-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "DPD",
                            "credentials": "ljkIEIyzleoeSE6GrLzXJXh9nlRkVmiWu+tEm023Qsld2iu0461qm3LK9ZmwxZ110Jh/PBp8E1hUuUd61B/7cei8QWZcF8qjAq6IyZnkL+MygqJrScSdbowuiFSJfsw2oKiNH5pkLZ37HMyi/s4bNkCTOCzNIF+QBeWDX7GEXwXAkBhMGUIrQcXrjvf/aJV6+9D2Wv3TZqXRrZHg8HYqL7KJm1f9FGQ5H6Fxsn5Ams7+qTcTfV4nxKB7mM2aQxLbPF2rz0B5UU4kKQgLjc6p6ISTm+HRkEPqo+TQMZU9diBQOlrEm5MPBDK/y/QKZf8SqtEG7L3VKSw5pbpyThRUvcEeWsq5eW+r3zQ1bhzOewYHHD3psQWUlWlWC2+ERO430xrYDiihs5gOBhtG5rYI15g5Hz7GrRSPXTJl2KHeOrwTUnKVdmgOTYFBNwiXB9yHAMw79394xLhEpgeoZAon59z+n/kgCV+xf3164Up2DNB4ZXeC0bKCwZS5UU1aqGV8imcBrsh45MlaF/jDeRI+ZoWhOUjGdJZrqibPhAKnOG0PW4028tQ7WUwl1Q8qZ10AQRqQMTIChoiTVr/CYJ+P+fW0redHDDXzi2jSa4sp9sPnsmkCIP0wuOkZU3yxawpi",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-08-31 10:08:30",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "DPD",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1448": {
                            "id": 1448,
                            "externalId": "1448",
                            "application": "OrderHub",
                            "channel": "interlink-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Interlink",
                            "credentials": "NhqQH5Yvo2mTPMAitLN8XOZRGJjNKbu4ld0zvfLH195fntGbuiTG++69OzEB8uB0xUkNuWl26t4ou+Xl3A3pG8Nj4doKnuE7Tnztrn82wVrGMkJHElVGs06ffZdFvG0s2MKehldhazxC4ycuEbjDX/AQZkOTULoat+XeDeujvZnN2xxB+o7xpx5FOjeJTyyypjoFa8MEtGQovHpCPYO7ph/Av7MU15q9doddvrARtiIEK987xXMSnei8Q+eauGWUs+74javCusSj0u5QKPLOoph/iUNtsU6XPuMgSbUvaNpQBIR4HVo/ztnXlOx8JeNC+TrnBQX13N+5I267uYhMNpZyh3I4jj2IE44WeJvWCCBCw+68U3UR4DMccBKx5ClJkReekIwl9D9KNO+dK1lEjL62B9peb1JQ+RgFeulo1XB4otF/cJXD9MeliZVDF8P2rR3v7QhyHfpMPQdOu8w2/blnjpu1PUdUPQhUVACqPNJjYpLLSeSWZjZaTENJs/lTTmOSUAMYMsVwCNAWQL8zpgxNvK3PmtStI9g4uNhRPUUgt1d+L+Pu/wSqkVhqQ24YbahGfPHKCC09QODqBBkgaHk0IlcVIsKLOJ5efJBCP79HOpeN5ZvZpBhhni+yAhDxeRlk996cQJGl85xiVHGgU6Tf1KycBa+SWeKj+y90s1aKVU5yLhEJL+DNeq4vXHWMt5KvQoA2si8GMUoKzDnP1w==",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-08-31 14:28:29",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "Interlink",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1456": {
                            "id": 1456,
                            "externalId": "1456",
                            "application": "OrderHub",
                            "channel": "parcelforce-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Parcelforce",
                            "credentials": "X5d7L4B6DUXntVIsKEC6J7ULjviSYN9GsxICofFbFW6PswrPlEmAdeK7IU7ZjFrFRTPaP7W6e/Iz+jG+KqKQNCLVF+B2ggau5v22zwx4KGTl1+9TYhkfhGHXhA95m2l5sVBSNOiSr9ly/kprrBXa7l22ouWiXYXt2Fzcx2VbDjYd4zAIN1Tp5N80alyfkRzVM/RoQJ9IwFVoFHqMXE2FVPUz5VAriZ9LM5DTJHUYuS2bZ8d+s8c4BOzrwi7NZhEzzsbWtDF9gKNRKc/wqKW3idSMPGvSJTnjCGMH9+7FxHXhYN9BE/igqnluhIxUHttJ7A4FQw3yEypyDDybfJzta54pGULumsMmqkBSOZ69YgKCrYpgxfZdhfnzmy8hIiAwoTOZVsgQbBP4rcbFyyD/O+pXGuVh3IDeclenPbv3i0jMu0SsVFDwI5QcDoostNQMbhCe/+nuTvREI1p86aJyAA==",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-01 11:32:39",
                            "stockManagement": 0,
                            "externalData": {
                                "config": "{\"emailNotification\":\"1\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"1\",\"sms_pre_delivery\":\"0\"}"
                            },
                            "displayChannel": "Parcelforce",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1457": {
                            "id": 1457,
                            "externalId": "1457",
                            "application": "OrderHub",
                            "channel": "dpd-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "DPD",
                            "credentials": "fG7mb7o2273LKUcn60vpLqfgxMp0G7LArSWNJBhjacburSEe4dIIXIwv33UPngs02UrfSOtf6YxYLQ6+efXNa6NbF4r1WlcVlbsQq4kBrkPtHWnfJE/IUgj6FNC0p4vqMB/3bwaV6f/gJgkSeTMmTGnRtr36icakeFbgOG+n4mBJhMpH+CMErlhJnO3+7Kq7PoAaA/1EZyHSf5hMBnrU4ZBrFEaGChToDRaiZGPgAiFWs02BlzVXAFLQou3FD+UauH+zbW1kRXCd+OOYTG/ew4yPNPB8SC3CCHwci5QiESVIs+q/qCApLMBVPVq6/EA8bghNsO7VllIRhUqNaHC/X+K9IePaplS38FV7nNd8twLayj0Fv7JSNqD8BwgVWM+p5geadxX9T05fQ5ijqfCP3qablNY1hJWDQMnxbvhExxjSO0BPvaafYOHE/HiokdsCDjLiiBCa4q48O/tiLMgaR0kjpFmD8xcmZj5+fPKTCXKd6jssI9pTEtoon9dQhCo0S/kF174ke7r6vj/9lKr2rTdVGlhNoqhhxNet3AeXppMk7PZ2JxpiYFQIy3CTuCs6Cce4c3Gdn1Ws/iSZi/9PpMhP/hvUxYDO6SMN5AmI7S0=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-01 11:51:43",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "DPD",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1458": {
                            "id": 1458,
                            "externalId": "1458",
                            "application": "OrderHub",
                            "channel": "interlink-ca",
                            "organisationUnitId": 10949,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Interlink",
                            "credentials": "xt5MOjdt8njG8ACdxh7Bj0wTCGxMqU0wJXIoG8bM/JuUSSpCM7P+/P7OhAjROM1jnbeSBpT9UDmfgc23CaR2kW/ebcVqrRMwWsxDoC0yzR/adLgTn+TnV7JqGWYV2Te06IV9otvmWG30mOSvrawVTXM956dun/Al/hUAM2E8CJFFnG9nG11DKXfa7CB9X9PeCGGHq+YRuK/n7xI+s7WblT+BU1YSIyhGiSvzKCYIrNtNwDjq7m8RqDsCtYNGAUAufF2pACZKU5L/YF7ClH+5pzwAFalqepI6GjrnMkO5gIMHA1qpoiYBDlSdctRGIeteCz52n4vvlwHAhWQKX/URUiRm8JYUdwCKcRvKw7SuYm4DN4nEnjA8oVNOA0zvMMtapgvuHQDX10NJ3Zwahh8BLQo2XvjFfi8uHkJRYP4OqbCULWard/8jBosdZOPtXJFXF0ZGXuxQEm/vjNOfz2wOlhXAK8ppZsm3YV3xDv8cIglkWExxs9z20i0IBQYjON6xmJLymqwqBmWFo9AK8KPZ15pmOoWHOgAcfpKduqUbNoTNEfnLkDz+eYnpXvWRj4jy5myjKUoi/QaLBBBK0G+bH+61cAgsW8bwaI4Wl1+Hqc8OlWc7a+mrmPpSNj7291kr3zO8oM+C1SASiXjo0oqTfiGiC9jeMSsLRiTz70gLn83dqaLkwbtvFsI9z1JPVA27",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-01 12:01:39",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "Interlink",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1517": {
                            "id": 1517,
                            "externalId": "1517",
                            "application": "OrderHub",
                            "channel": "myhermes-ca",
                            "organisationUnitId": 10949,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "MyHermes",
                            "credentials": "h1S6HNiK87jLSSQ9mQdgnFR3kwFgymBbpYfKOKTOqA6W2GDiHqs4GRc8HFPlDPA4quy31sQY+v7aVieuGjrewV0mV7rExhiVhpnPE2e6YOr1OVk2FS3VVfVSKKhcMy4RVBppxlE1hPgW+Mwe8WHtW10AFemp62BcTQNXsSIzfMwNJVjYpm3yZklFUMWUiUMqJAsyi7QkZUKVOY/z36k0FVgYPMjeq+WdaUm4T8jvmVXJtLJheQjpiYD8C8vFutCMC5JwCEAOJp0EPUiniz+FhkOI7b3s1U0wUt8R/aI4VD+R4JnHhohsCJHfupcz9xMQbc+3FeociXqZJJ8JLHZSRHE1g9FcBCzHHAMaFT8z3Tc=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-19 14:43:13",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "MyHermes",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1518": {
                            "id": 1518,
                            "externalId": "1518",
                            "application": "OrderHub",
                            "channel": "myhermes-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "MyHermes",
                            "credentials": "KhhFj5g00yulMijSIH+AIhJDZOKhkCOvK8bmDmK5ZiL08EdLU1JvIkoZdb+/nLDwuv78t6Mkh7zjHnj2QyrDzVBYmYp1gIP2otSCu84PvwFEOgRIEGKXIp18kwYHMBkhE0HryaoBVwYnqORH5/vhVz2rmUl3q33+6F9oeKIEGziK5vqf8TjDXJklCGCahkQe+zjY1cPzQc43pLaTI8meQ6i5Fc2NtMglKrStfE3sysmOH8Qw0aNHzDs0R6egbZvvxbvcYDl3bqk6qpllOE6dqUTYu5OSkYXN5ckY2BzyuyjgpF5Qbt0ytCFp5WhngpdcsAzPBSJsbxLi45+KvUcnBCrtlxCbS+0kzxyj470rTR4=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-19 14:48:47",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "MyHermes",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1519": {
                            "id": 1519,
                            "externalId": "1519",
                            "application": "OrderHub",
                            "channel": "myhermes-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "MyHermes",
                            "credentials": "9ss6kvrwHKcLDKIZomeQeosQVceVYY83oaK2gEN9EWvxyZBgc36LXjeFXjzCqNdZgPJova1NC8lsOmxLEQ6rQkqJQ2ORcwP61RON3qtTVmOtbRodGWI8F6Tif5l4JVwN3V6U0eYv0HeJIAZS5P+y+qiEWNteN3oMI5s6O2Z5ROpFJm4Wmtr+mWhJstqHXxzfVowEr0jgzVV9ovv/I3ovHrm2oR18pJpQ8F9hmbKlWS8Mx/tsuprfKDXHB6yY0TtY1A9rVP/yVR6idBExH0WovwBSiWH/w55ZoqTdrNMvlnIIdQ0VBFezjqlg26DQQsvfnbWToT771KD0iskq+2HTY2vwjQndkoFFQXO0aX0Gr9E=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-19 14:49:22",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "MyHermes",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1520": {
                            "id": 1520,
                            "externalId": "1520",
                            "application": "OrderHub",
                            "channel": "myhermes-ca",
                            "organisationUnitId": 10949,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "MyHermes",
                            "credentials": "CktJet0qBgjEGaDOHPUDWB6KCpZAtwnby4eUv3mGBDbA/xa91TRzfWZp1W4R16wOwB/A5EXvYZMpxQZg4XtdRDnfapKH+QpL6zD3ppFnXlwU51dBEq/X3ulpR2VUSPoxzaaKsFW7BsopLwlNMkBg6XKPiy/VBawcdGocWFgZUEuptaBhywgJaX34BV5ozh3aECMrB7P7zHfG2awMDizXerCg2zjeiSr4oTiL1ohbMMMYoA+dr5JIWrCpk+KIUSymEkjgeHS1eOSSr/XqoaZ8RrB45XVYFzIlOXEsGydlGA3VTVhCNE6E6AsLmO0pWopCSx0aQDc7oUk04KDDFaGSg/i3aFMIxxL2s1RzO+ucQ0s=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-19 14:53:24",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "MyHermes",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1822": {
                            "id": 1822,
                            "externalId": "1822",
                            "application": "OrderHub",
                            "channel": "parcelforce-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Parcelforce",
                            "credentials": "S4gu60+S+paiPJMW7SRwvl1pT6QI2trrbjLFXSU0W52/RtzGLT10+TbGnh65r1EL8/imaGzb67YQgbU/zDCE3v763VAP7gKfrq6ifPlHzluRaXQteGpmKzQQWPp39q6XgTzzAANLI2otAzTrQjZXYY9fUCaYdGyR2QmxOdhfZlbJQBq7cmvOHk08fPy+3DYc9sIGnOXLLpdS1rJ2apJWY03oS6d9DLwXRvfKPrwHW8mofDFl+WK4gZyRUcrlLTp2v2HrzDw9TPkqs7chL+COpbscgph4soytOYCrl/Tq2gAqjVjoC4xaUCzrbZ1RY8U/GpxFcwvJW0Gi6ZgU+4UEYLFeGa5He61pExi+bmwp2Wbase4DJjfipO4anqqwySM4iC/xjKJMg7mD8CLWoLHqzsYw4ZvrM7tg4pQo8tBVDlidp7S+DVg6nDMgogppJ4XbOLW+/62n18TvD1DJNSGLDQ==",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-11-16 15:41:22",
                            "stockManagement": 0,
                            "externalData": {
                                "config": "{\"emailNotification\":\"0\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"0\",\"sms_pre_delivery\":\"0\"}"
                            },
                            "displayChannel": "Parcelforce",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1823": {
                            "id": 1823,
                            "externalId": "2015291000",
                            "application": "OrderHub",
                            "channel": "royal-mail-nd",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "CG OBA",
                            "credentials": "hCrj92WJJBP/ZwLM2YAsOUQpIJgNNHR1miGevkZ+ojdpUQaP02BEdQnku/fS+aZPgK+iCw4eoJuAD+2qY+FuIL3BC2jlfZCvlJ77R+bke8Mpp3/iUSFCenaWyxSbCpId4AsgilA75jx6vp9iJb/JfJ4E4ptVX+xXKkbasftz6zahmX9ZPGyQ5xF5EuPoT5jIPi+1Nrn7NwczmdUgFXYELPjeVCV3Mu4+Fktfd5u15sL0IN8N221wSo/iXsdEb/JkXxwiyyNaUJpplsdrRTF0tMRaHj5iz8NbxjzL/q7DZh5E8zHHGcCbWoQ5ZdRaUWEA8W6qF3Snxk/Q7KgHdmFYPMl82/MFAuqVgJN8JDVKSGpoxdB6Hew4iC1cibJOJYJbyTS/j8VBiAOex5jLRjwGpiX8cK+7tsWRdhcP27uX8SgZqvvBImyH9kp901/V5HYFiDGVJtd8j8zNpTVngEJ9szBrFVKQSrOvob4ZBCOLASpNP47CrYYmcYXcuO0hy1jGvSL7FFXZaYGUXFcZsZ84SxpgZR5GjwPqF0MctEs85xw=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-11-16 15:45:10",
                            "stockManagement": 0,
                            "externalData": {
                                "accountType": "both",
                                "formSubmissionDate": "2016-11-16 15:45:11",
                                "domesticServices": "",
                                "internationalServices": ""
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "3086": {
                            "id": 3086,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "ekm",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "EKM",
                            "credentials": "gwdBr3TbEXYietikBI+mOpc/BS8iOF8h5kCjQkIjHJQteqPeVH7Kpb2PHH8gA4bUD+nu2YnmfNKW8BNqRfvrwxX0jGIXBEBpk/BXbyvuu0KuYyoguq6K3iTbaM7awC2acUBeK5SpaRSnGYB3zODVtFY/6neMK9b5fQOhyWm2itMphSkEicN/9g6z8/Q3myo/eT7Wj1yf2SaeyA1zrp+MwzrbiVt5/800uYARkIvqqu1dYQdKpKcuHH3a5GA6MLupbPB/CPHldaWGnv2kIdNWiWz/6SVSJYI7jmru2Qnvt/mdHmFHjXXOvNl0b/bZoQaEYm9xwCPC6+14hS4bsFnFqBqoaDnl8/1PmPXoOofQ9WQI6Tuhncu0xVJIONdIN6zhIpCtKK0KylBQ5OBnusHFDUhy3F5WFX+n3K6+WbVWbNWOCqmDOdePsCWM9pTBFvpPJkmHeDeuKfu21by9Gpc1KnnwKdUcmWX+X+8kO2m0mgs9xOrlJ7+WC61TQe93w5/QIIPRmC+CjVhOawZwg16M9U89k26aQMoEWAr5PA/MLIlElw/mVdlNwVYig18fh9hPBlJMcpHh5YTFUosyj1pP7fcDImmodxyoH5GbF2elwB11sfNyALsNFvz7mDBJt9Bec3piOaS3mCGGkwbbuUMSWhbpX6Gd/7hC/ZCp0lvon2AXt/pNAPKZYsPURnShF/D/SbVMF9qDFO0Fd6o8dwsUAPFPIBvDl8MfBrqs5VKa07BqorFZ49QiOuR5jTKDAWpY",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": "2019-05-31 22:02:04",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2017-06-26 15:12:05",
                            "stockManagement": 0,
                            "externalData": {
                                "ekmUsername": "channelgrabber"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1560910068,
                                "processed": 392,
                                "total": 392,
                                "lastCompletedDate": "2019-06-19 02:13:15"
                            }
                        },
                        "3169": {
                            "id": 3169,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "ebay",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "chosen12010",
                            "credentials": "UA2QeZxpw9ep8cbz5gSaMPOI2S682+fB8XnO7q4128yBxcp2d6tlGt2M63SuwTLOnC+IZv8K4nWetiE4o5APCqm997qiPh1GnrNxcwA5b5uhhDOz2jFcSuJ/F6Yl4QjDQLgPTGwTuoVnXob88cO2d1SLEpHkGjXEtR2XOMi9RNW85FyfQpUtzdO9GmSaExjNiGhPGYeI584qaMJxCbAQ+CPLq8i+9Hyg1L7lwdPONSVsDGruBrDX3JeAXdSEV+MD34HWzyAWqqU2NePseh/zr9ZVLBzXj4tKq7su0yrpbq6knM8mCzqZKom3zxeC6rw7R5RCHDrmP1HkzvS0PpGjWz3kx6/9GJEVIMMWJ3JqglQDiWoBalXT41lbtxFOuI3140msja1qdmavPesf5ZAwHq2ryCCtl7DlRLYb5m2EJyH/mPYS0XP+f2n+DgDNXtQOtclPyR/olO68VClw+AoQHSDnqyh3Zc4WjmaLhpsDUz/5PBwp+dm1NQhTlzZiEXk2RDCScILu2ZPddMOclnrn2a4QXwQGag8AMlq7p4sBMU9MLvO073YdrzcVvxNaXnxAoaIZ1WD+W6OdA4SNjdmlNcB2gR4pNjm88paG6kEd+SGGgTTVddA/fee5kS0OtI1S/ydgGqjLsPYIJE+kTcEFc1uAOkyRVMNTN3T2zwjeleRsLTZ4PkXABDokhmO/r0mo6dxjThId+xA7Sq6Jg7MP+Tu6WveS/UbjQrtC8NicbTgxPzu5Xa9rCcK+/HyC4zwdFaCvC3MQSaGkn9MvsRcVKQklQlOISZaILV10B3/4YM5JsDmDIZr8V2hYefC7JuvVXctaiGDRbsw7Ju58vTi65dNXA0myisoNDR0cai/EvNwHszYzaocCdX5af7NXaeCxX+yitu/J5EPMmDDEiFAND9Tsf1wf/bF83bpalEpKsAaSnbvn5RE+6M8xC+oiW+At8zBLEK8SZb4fzfI5sj96Eu1qmpHTYnAUxvCzcHZmnZAlfmji6t0EfxSe0NFulzHKPPcuoUzgFFofrBhDaDXBteqMFquufFm7+vAp4XsVKgA0yAVdfi2U6niUhhw3G4D5zDbqyoKAkNDS1gnVK42sAAInZQGimQo3xO+I3nNECsbg6eFSWXwHkfgAnAq+neVZjcYm6o+qWv8To/MSw6hWE8yJc94JDu4nGKUFLlv0xE4D31sozgIYDhLggqwTAYyVIzNguxEDBo2t3cCcI1UF/+dgKXrZ3wiV4YWZJJxz9MKcLFm80P/2RITcQN9W3eVDN9wX0XPJsMYNyJp3BTbCdifrOk4hoF87oI+IeM6369uLIq8LN0x7ZHM2+GrZfXh8hSBiQnV9H19JI32/45QXaR0TU1BGVWXENy++mudI8F1ear3PDtUvxIg1kM+qHNaGah6braiv6XkYRLlH6b9YYk0BPQjoCwxyQ4HIzs5XLhr4lRoXA1rxuNeMRwV5tT6gBhsELw4vdVzKDDwhBSPb0ei/cbqc3oj9iYppLI8pwGoCQn/vBqDJaRJHoJcL9ZwE4CXFqgegU3arIGXqSFvxqlXQX6Uu+da371pu5MftsABT9j1yJSrXvfGxQrISF5SIz8gzgSHa0o5rZweOaqeq613Gwvsi8lpdMwCUX/kTGWArxeVZjySm/g28fvUgztpZGkAKcCVnSH2bzvLcvaqP6X3ezUAdfG6y+Lv/dHz3ZUFBTRGU+UN3JYDcL88vCkKrrOrsiXSVlgpo8+chOtnKjxnJFYnIWvLj4+qCBcD1NPw=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": "2018-12-27 09:19:37",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2017-07-05 09:19:37",
                            "stockManagement": 0,
                            "externalData": {
                                "importEbayEmails": 1,
                                "globalShippingProgram": 0,
                                "listingLocation": null,
                                "listingCurrency": null,
                                "paypalEmail": null,
                                "listingDuration": null,
                                "listingDispatchTime": null,
                                "listingPaymentMethods": [],
                                "oAuthExpiryDate": null
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            },
                            "listingsAuthActive": false,
                            "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=MzE2OQ==",
                            "siteId": 3
                        },
                        "3170": {
                            "id": 3170,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "ebay",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "eBay",
                            "credentials": "YnFTJPtcN2vdvLi3beaLxVmry6ZRq1g7FaltD6+W0fYrDGon1EY6sPOLqIjyUz7LfDd4GSfFV5X3svlWdNQyFNqWO/0nvF+Hte/I+wfv0C/mKOYW3yQ2cfdiTqAcsTkAFZC7gmnS0f42KDKntDyqNqLYvfoMH60r1f5z7GFTlIeeJw0ewRw+Uw5TmveiAcb9Q6NPSycQNxnK7zeAOUvZZ4bsIVWdEwS6wX7K7oP/zjMlSdPau7+E6BjrqkuyfNIDS8Tn9xKvwmigNn2yu1tp8WbrvXXonuAlBYZcmVX1nXwdvw9sMyRQbV1Zm8HB/tR9DloBjadUybCl/dlWFWovHd6xA0d3vgXLDOVF5LpBPLFlOmaAKp24f4Aw35vR8qMW03A6+s8jJvdSBkepwrvlNTiK3RfAx7np3Z9aSBA6P2BVpUrXuUvVwFI30Ub7jLBmIjIIyTsHOIpiM+XPBrQv3g6sdm2+oPh/5k1F+M/6ZAM5Pyml+lgnqEiGdF54EXT1cZiosVxcThC8Z5cg2XmbdU2ZHqQwhArbzZ59ne1moullk19yGywWK3JVeGhy87CHqyyJGZeo1MB/DAikuW0t1Aozage5nhGfkiBzcsrRT29PVyFMGKMWCqNLJQ56dXkllbwd6HuKsxZwGTwnaqJJRWE8bRpaytOjAs9TyUA8Ojo7/+Y4T7ozK3kbP7RMrPeLFFM4rBCCJrsjaDHM3IQOlAQr9AbpOwrb7faBtC22xdXLW3l+WDo+EpulNQ2gNdiyMO9pBinfARuW9UblAJRosEUsw/tgFM9rz55YwVFQpPaMFfJe2EVVWiXsbNlIowvvDARBu2CDm9Ti9my18LHYkLq66NnKiqSwiK1r9fT8jw3nb+UtdTvLXgRIeYCkGkxLcUUGiIdtcbPLdC6U89kmNjcnoTyl9gJQ1q1WzzVGI8FWIy/YLJBGTTRy6728mFnlWrPE6JyCDAidb4V4RE5BQNLFJIY/bICprRoLNUHrIbhjiujhcU/P12NpxtY00r+FdAJmxMO1LnPl2QnNsG7pfEu093Mof25j/NkT973TwdrO/yOsd8sR9KxhzmzKHGl5l3Z2QDyMO2Kc4/mxwUfm6J1Ns/Z3K9eWjLDntck3302oC1Hcm7sVTx8xJ35sTX8VzvBBdspWsavyDs1fCfvwSKhHK4R1zpTTh4a2ZEJV+M6BroxsELe93/3mwTKHhVKK3U+xsLx7LevcRdDpIo6rcP2wylQeyRXsw+d+tw6bb7RTHL3D7Mt5l3dWDpV3KyYGzEQZao+2lm224GvtZd15Ey1FCOBVi/ks0+VYK5bOaz/bPLNDVqCW1deOujg6V0kyHm6Iy0LROngS38G2ZooN8gEaXbUdE2muq7ORC4yXgs4diVPuQo63OubPHMLctFNU/LdgSjXGFyGbzc/TBMy8nxMsijPtVbBCt3A9oUFeIubRLlP3OkXmjPrnNiLQT3NUABFOrDiIH+6DA3fNu3+8o1JDqWcOKvQhRwsRXskuJ5WSpEW39vg8M8dO/F3V6uYe2ET9t2bQhis1CuZmEFo4EMghZZE4+6pco3v+wCTi4plbk/Hf0f9MNUHA8MiGMkCW+ZsNZX5mV24UGbGHcPG+D5LyNPIeUYJTto+yomDArATe+xY1m/PNPgftBqEngESjbv06xXwL4NI+74nch7KDFuWyLLGmZxsisd6u52jybidgkjJzUFrq4fPRvDt8P+XLBjloFlBovlaOjeQGEqJ+2nrBvGBFyvYGrUxiOmXab/6B1o514kFuqz3hQeu4UZjy8Wv/Or9vv2KobqNG/SQ+9Q==",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": "2019-06-10 13:30:29",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2017-07-05 09:22:16",
                            "stockManagement": 0,
                            "externalData": {
                                "importEbayEmails": 1,
                                "globalShippingProgram": 0,
                                "listingLocation": "Manchester",
                                "listingCurrency": null,
                                "paypalEmail": "accounts@channelgrabber.com",
                                "listingDuration": "GTC",
                                "listingDispatchTime": 1,
                                "listingPaymentMethods": [
                                    "PayPal"
                                ],
                                "oAuthExpiryDate": "2020-08-16 01:02:32"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1560132005,
                                "processed": 0,
                                "total": 0,
                                "lastCompletedDate": "2019-06-05 02:00:15"
                            },
                            "listingsAuthActive": false,
                            "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=MzE3MA==",
                            "siteId": 3
                        },
                        "3243": {
                            "id": 3243,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "amazon",
                            "organisationUnitId": 10949,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Amazon EU",
                            "credentials": "7KcoJhcMl9cT5rTLVAL58CRe2hSr87TkfK9zdFb2urppxk7EPnDoisnizeXmEKMFeYlaBwVoYJG/J8rIB+MwSKJkisYeQ3Az/egy0uwOs3UE7Z/UunXuA5xMRIZLI0GkzHYH0DW/67U6/zrSNdNuJ+QXgQLiKIVp93xExHup3yGD0YLHvxNSw58/RoWrRLpL+TXpT6X9VHfigokGHrIu4uwRnyByUyQHaAyeUGBxItx/pbeldHE1T3LJoY8JiGkoQwIUYCiE4jqoLHCwXqzZ9pWQG5LFNiSaGkA7WmOmTsWZbymL7Mg/YA7DzyjuFXS1mfdDFCRN512M1fshjF+X4psCnutqb+k8UgBvEHGedBJtQrfecrFRmkovlWD0Po62JCzpJAGCKIQ6IZqioKZQLEhD4J5AMcmx+CiVHku52/5WvByALEWvk20Mu4nthBHMnrc8whDd1HNzL+Idj6r6PaebVX22E1mb8w7pUUcu20lA9LNwcBoT4VDHHXtO5nPwD4vsPAUykf5es2vuEHZfhXDN4kdJ2s0ZS85dqxK53Qc8IsI19Bifb0Z4ofOFhFApFJ+8Bn6/Dcm8KahJzH76F2cj2bSevXflDLtlbSzQjILyYP9UifJncZLriu6mHKv+b4oshBCzIl03MdWXgoLUrHhgOiGB1jctzTp92TYX0iQwomQDHudS56ZaGGDBMlJtED3F6ojBhNBZQy+vIeBTcbIBgs60H7PgAjbPACtt/nN4zlw/eOFQfUEyWW3g9lqGPaIGwZJFUZeFC1F+DmfyReyoOqOaHhe9HRYWxFVx/QDjTZReHTHtSFIsAVX7HH8lyH8XnJqVKqdIztJWmikcoyj4Djj/LtcOFlktZFRHtZZFZW9i8/gKWj5LuA0QhlwgteoF0cY8KNMqYhpdghRggN3mU66tiXl0hSHLbLsPSORRdJgi4esMT7jXzvFVXhllCqycx0EkrIbnz2HTndq9b6lAdDTxxLIEPcdObHnOAPLPWxlmrxzxxC6922DqkqlK9bmkKtrTHDuN1DrGP2p+Lf3mdrTGoskYwVJ2x89mR0QnnQtQM3jjXst/ujwZfRKtEOMNx1Cwq3uqOSsCTt434A==",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "sales",
                                "shipping"
                            ],
                            "cgCreationDate": "2017-07-11 10:47:32",
                            "stockManagement": 0,
                            "externalData": {
                                "fbaOrderImport": 0,
                                "hash": "3e474f1d119d973829736ed9f7e18a699ff607bf",
                                "originalEmailAddress": "",
                                "fulfillmentLatency": 2,
                                "mcfEnabled": 1,
                                "messagingSetUp": 0,
                                "includeFbaStock": 0,
                                "stockFromFbaLocationId": 2796,
                                "regionCode": null,
                                "marketplaceIds": "A13V1IB3VIYZZH,A1F83G8C2ARO7P,A1PA6795UKMFR9,A1RKKUPIHCS9HS,A1ZFFQZ3HTUKT9,A38D8NSA03LJTC,A62U237T8HV6N,AFQLKURYRPEL8,APJ6JRA9NG5V4,AZMDEXL2RVFNN"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1563850808,
                                "processed": 49,
                                "total": 45,
                                "lastCompletedDate": "2019-07-23 03:36:47"
                            }
                        },
                        "3250": {
                            "id": 3250,
                            "externalId": "2015291000",
                            "application": "OrderHub",
                            "channel": "royal-mail-nd",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Royal Mail - NetDespatch",
                            "credentials": "TPAJ661Zv8weflgLAfGnxOUuidrIqYz7h2VF+DXL62F97ORyRp43wkY66Xf6AidA8MPWZNmo3QiBHLP5T7/mOZ7v69O1wf07NGm/1G9TvAH8RmJXxSmk069W9MTANKOgEkaKQYpSEyRG23qfYHx5bHgg9gM9+ljgEbbfpwVJSIMo0Ve18dFSGz28O5y74k7tcmbHyFe5NWjR2imIEkvQ75Ph4Dw6Xj2yY3d5W6sc4EjiRAJ7PH/01xkQFGuVkbFIARVjk8qeMnY9qOjuqrWoRUrJEpldvcuTj6VSwjtsImEDc7S8vcBuqtuLHQACUqi0em4OSOsEUa6Uty7rodNGhArJLkhmHX5KMX+tjc+tqunrHgTk1PW4OP0gqJx3PqFKnS/DWlQ1DzNe4/OhBzwQF2+zi1ovdVJCtj5Bt+L1fpYGad82rwHa2j6mTTnQXPNGaa3uBpXKDALBz2/s/XZNIXElriH1/h+UCMupDfDiCiDJ2SeHU8J9HnraiYswLVmlUqL468lqoL/9ALeqXeLzIW0LM5lFz6df8cpZtTCV0Ew=",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2017-07-11 14:38:55",
                            "stockManagement": 0,
                            "externalData": {
                                "accountType": "both",
                                "formSubmissionDate": "2017-07-11 14:38:57",
                                "domesticServices": "",
                                "internationalServices": ""
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "3252": {
                            "id": 3252,
                            "externalId": "3252",
                            "application": "OrderHub",
                            "channel": "myhermes-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "MyHermes",
                            "credentials": "7f0kv+R/TAgt0FTTQa/jAgjjqKeSJJEqscqyXH7V3jN1DE4phocq1MLfFFYGJ0a7AqSagcRmtARNBXvSRlw3nzboLdHgfTKdaOybiEmDmID2zI1cmpNdhi3h1wPelhCWOAkoPGPSCXyndPc0AzVDWHzRte2v76B5WJM7+QuVKgxxELxEMjub5BlN/WbQhjho/rCSTfQPW5Dahflawhb8eRPGKgFq0IdymRAikIXylt0ofznpXkIgxdiqvg9duxViHxmPQ41643IDrsosKt41Bm66fYg4e2WlU00l9ryf7upXbOlhKTFpvHDEDBy/GpDIMp2uHWf10fz1QzOjxdK5YIGwJF2mO9br6rFoN/Y4AVc=",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2017-07-11 14:43:26",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "MyHermes",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "3336": {
                            "id": 3336,
                            "externalId": "3336",
                            "application": "OrderHub",
                            "channel": "dpd-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "DPD",
                            "credentials": "gQR0bS/8kMl1ZdPqxuByLRgIzhL8ifWeqzS9R90xxZP/0lQR5ZhTPJHxLRD7FfS8PLepaPkA34Oix82txMxXWBzj2oWxO58d6+NV8s7QjYwo2Nt4TcljqCBUa2q9ci2bXAAEaFr1Rmnn/Q/DfLyS0NWbPbDjknBP0+//MK0BHZaHU4sQlMTe621Bor8up9S4jQZpUJpc7uksyCJwxG8LlhzNLlOIB7bov+KWx4zfUtKs93uGnGlfoUXylNVFCCwqJBJkyB++HMZIY9HPslnQ4doB8U8zwTV0zcu3hUdCWahbeEPSR8/zIOQn9GyOftzEWqa/3qB6VwLkg1DbtVU8DyCIbhcrzLaz9sOkl9XnMid+ZT5Gp3+w0auL7svxqiKSjmDvG81uifFbJxZL23Xk8EBpl9Sfy2/kTwpVlOlB4sy0Mm2zN1HeiJXb54tPBt7plNPYFWtxF83Ij53uL+cDpPzot8KqlK4DPQ92Yr6xRqoAqY1Vsw5okylz2k48Rw8q7sipDQYuza+A5v8LMjGVQzpl/gl8rh/WtcNxvyL+D4vSWafxx7GfIglUfschK9EZgxp1pC26UWBt7B41zQQIaSqKRmiCKkN8ZxrbQ5TyHUEY8LlZwJBwGxEye2qW6mL2",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2017-07-21 14:20:27",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "DPD",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "3337": {
                            "id": 3337,
                            "externalId": "3337",
                            "application": "OrderHub",
                            "channel": "interlink-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "DPD Local",
                            "credentials": "VsgnLNrzpX0lbxDf762CWR/zHY4J57fFZIPPMuHBWSs7q1woeJfGIhJNV3Q8aJufgNlA6g9ipRofQjMMT1GcGEcus5TQYzLrMag3PekIzx/LeW9HxYeJlS6+mVJ6pNki09+ePPLJfUhkZ0DgTghmh0Vk2/0Qst1ur9IvRmdjNoqDtCF9avYJlq3q2nxxf8erS1x6QwMe6h5BM4vOwJc44bt92/ioK6q+KaUJYrbEKDTv+X9skJuACyDVkrdC2o3KquD3ie2hKWfQ1BeXhlfId1WmO0KXFKZpn0bVAIsYPadjlubUmIxBw0IZ2vLMkkVQeJnmIIdmQ1ZDpD7YvZWNN/wYzHavIBa6+0UEeCfr1AlSw0bhg/F/ZWn4j77njvalqMbDQjFaiTALO2NofS3htvF4RQZVwEYXEKwtYZyaiRKotIY1a2tYAWOQmVJOluTPZfwD3WTlvB26xfE6ENiK7sgLiE6/dbrN4xTjAsTAB6d2Z0Hxm1Oa0xaUIy0/Pyg9oUUCkwzAlyP/dpXbtMUQiLbJxJg9uVMLw44lBf8WXVz5KcJK1/Hw8ZDfPWMz+dIj0uInuaA0kVyxz/xx8IUKSf21a03DibXKRGKDM0WSlhAO0+1gSqPQPLkw+srgAg0sRmzIXBk5rpq8opXd1QLv4ugYvkGs7rh+OU7mcl0rNIHIT+m8Hpn3SDeW66PYhnxqtmX2PAJ7K+jDQaKxmCguEA==",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2017-07-21 14:42:40",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "DPD Local",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "3747": {
                            "id": 3747,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "royal-mail",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Royal Mail (PPI)",
                            "credentials": "Royal Mail",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2017-08-24 15:38:05",
                            "stockManagement": 0,
                            "externalData": {
                                "PPINumber": ""
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "11660": {
                            "id": 11660,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "shopify",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Shopify",
                            "credentials": "2nfg+u+z7qgiHaUqL7yEp5wBIVm2mDW9bW8IRa/tbJ/NVwBsRfGfZD3QNc4CHhkjidsA6bUMGlGTIcVhdvsB+yEecd65eRhg82xhJ6Phmwg51zsVENmCtRvuQ2tjJGpibW3M8gGAW4IJ+5eAdJbvG9jT9+OqlLLGVK4FSZ9+iQoHjKsQ6DqoQd892BOl7dFkcKLmSbKEAXQQXkRU0D9sMbecSmACoa0CSfBCGTEqgOE=",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": "2019-06-26 12:25:37",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2018-01-04 16:40:43",
                            "stockManagement": 0,
                            "externalData": {
                                "shopHost": "dev-shopify-orderhub-io.myshopify.com"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1529671637,
                                "processed": 1,
                                "total": 1,
                                "lastCompletedDate": "2018-06-23 12:47:32"
                            }
                        },
                        "12354": {
                            "id": 12354,
                            "externalId": "47fwg8cpdt",
                            "application": "OrderHub",
                            "channel": "big-commerce",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "BigCommerce",
                            "credentials": "nYg4OGzKeMmDeVdlbKJRBg3WLTlxIWOK0or+WcNloytYBe91o1iu4DDfhTuCiat72+b+3G8yCA32SCXdB2+mzBxnW2mkSfUq73gHOKhQpNkBiA5dT+XYfHokcdHJErxWps+QtofTX0zFmINbvZOvmAvPpLdUblNMW6DI7IJ2wHoGFlo0UjB4b1PIZJ3HbgDZRD3RQWCIHbXgxpuOAKAJx6dHVtnx6hxqVGMrXB2CApU=",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": "2018-07-30 15:12:37",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2018-02-19 11:20:51",
                            "stockManagement": 0,
                            "externalData": {
                                "secureUrl": "https://store-47fwg8cpdt.mybigcommerce.com",
                                "weightUnits": "kg",
                                "dimensionUnits": "Centimeters"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1521127671,
                                "processed": 13,
                                "total": 13,
                                "lastCompletedDate": "2018-03-15 15:28:15"
                            }
                        },
                        "12355": {
                            "id": 12355,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "ekm",
                            "organisationUnitId": 10949,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Acme",
                            "credentials": "Xu6vYvSef2w9DAA3fiZvhLYknLkLPyN9lGehw++yU23TAPfkgFESdOvWztrilx4/twu7etv1q/3xrxpQ2ZqZ6lE9MqsxovvQCRYZRTD2anY08cuzlDNJ5Xm7cds/SPcuA0bLkbkeO2SFgVqrc7cF4fJYfO/FLQOY878LYaTvFJL9xT8jx93gzf8TGDctB1IABpkLG3kaZ/7t1gD5adBukAbhzu9CA46r0YyqU4rDqFTGDS2BVp1z/p31ZFTElA42nRsHJdoJ+Q/ICfjLfD+NlELsRWne1dp4Y0x7FzZ6djfcS/ZvtWoPexv7Xz0VcGdz4Bz9odLqI50TOFJ+GPVOeE/XX9k8Hk9Yx2P/j1R082HZpK/NZlKdCD8ovh/g0oO4dSaZNYpKoZs3uUWogN56GvpQlxUf1CtorODaTTwBPPbSpSAEzzbtMfzJJSdbot6lM/hOyurtuwEAmao+V4jZwX7Pqq5DqrTXVZcHTtSUZQ2BXOP6W0ug07rcnXAXAd9dNcmld5d6ywHFhO1U3osZYrbat5niVRORBdmAu3842KhfuHefTnZ9D34H2YpqcR3wVsEd44oPgSY3EJU9n/lvUj/Aohn0Oz+uoOXlXPCiNBl1wQQ61CZSRsQqCF4tWgDdQsUb0wEJJaUSJ/JcnFBiZyEeTk5TnghbxhXSpSvuDcBNgh2cRUhal0mf/8+zRY8rEymTDzRxXoUd9InS3YJS64dfwJvJ4OOmq0bI1BXlMXs3IX/XAzS/Cyj8oAcw3eIx",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": "2019-05-31 22:01:11",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2018-02-19 11:37:54",
                            "stockManagement": 0,
                            "externalData": {
                                "ekmUsername": "channelgrabber"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1560910676,
                                "processed": 392,
                                "total": 392,
                                "lastCompletedDate": "2019-06-19 04:34:37"
                            }
                        },
                        "12628": {
                            "id": 12628,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "woo-commerce",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "WooCommerce",
                            "credentials": "n74zEWIEcNi2aN1IiseeY4EBTnZxbT+pIt/V6XmdNHJSxK/0SPaiiSWKFbV0qSJtk88YwMaTf2wIgN+32hsCPIertpodyhhccXY1D5C72z07zEe5R48fxaRtPOcZDdbcwQzKbdz6qMta9o5ZyAlsVqYmezGrQ+tugX0sx4wACg1caskGqzGSjsrXMsSzTeG5/S7B1kT9qhXXE7vGBks03Q3l1RK2lbdd1ilO1WorAQZVtZuCugBuPuINcADQ7RhkqloG7UALR5QuF3oTdofh5ZrqKwx8c0FCQErZKn5El9iWO7NvgaHybiizYrIPDUoAacRxpJXx8Z4BjlSrItmwlIMC1XPr/jzOh9CVU/9i0Vo9BkoOpHGXP0ykzP2fHdw1hRaV3UbnEe7QnR5Oqf1t5wdfCNOuVEG3cNTJqSY87l+6XBtN0918lq1vT8p2A5n56GuKMRsuzrb5afgNAfDmXA==",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2018-03-09 14:20:55",
                            "stockManagement": 0,
                            "externalData": {
                                "decimalSeparator": ".",
                                "thousandSeparator": ",",
                                "dimensionUnit": "cm",
                                "weightUnit": "kg",
                                "taxIncluded": 0,
                                "currency": "GBP",
                                "sslEnabled": 0,
                                "sslUsed": 0
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1555459231,
                                "processed": 92,
                                "total": 92,
                                "lastCompletedDate": "2019-04-17 00:02:27"
                            }
                        },
                        "12917": {
                            "id": 12917,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "royal-mail-click-drop",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Royal Mail Click & Drop",
                            "credentials": "FyGQTsTo6FxxEgfZP5u/mg3S1GYllTb1Fy7Rs6Va50hJKMOPelFoKHpygFXmXHysCbIR9GpAjIdCpopxwHuwvFAe5o1azYz+WlSKG8VGPJuDDFPhZ2dFPlW2s8DScHpjFO2TnH2D+7DmauR1W/Ttm2v8FIoWY2Go7+S+GE3fq1wZmf3eESt84Dn8hsx39lzz",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2018-04-03 13:25:10",
                            "stockManagement": 0,
                            "externalData": [],
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "14098": {
                            "id": 14098,
                            "externalId": "14098",
                            "application": "OrderHub",
                            "channel": "dpd-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "DPD",
                            "credentials": "uUXAEpiPi895LUMm4TvfhhQDFNZUt6mMfjkRyqEKtBODeuPnx2f5EmQUhp8AQmYbMbyM6B7amdpHW0alXAG7gt/ufw1Ndhb+iK/q8if8V6pNK49HGba7f1jY5/0d9+QEN0TAeadR0eSZeawW1BweyvpGx1b6sLF21ejgecRKHPtFPniv/Ym6EO26D9OSyyZSIZygyintBBX7r9fnCiCA2BRk/IR49CrdYTz5jeEubd8ARsY7MJXZjE6O6/TePqYKzXio4Q+GnA2i4Tc+dt9WbfaiRHQjpT5daot6wDEWeIDtm2fe4w+f44uuitY9S5zSVdcMFO1Piu7LPk6ohoebtoK4OQUZpQVJDMNlO8vgX4zbgT1GxYzDx1FdVtniKnp6eZbt3rp+2h3WNbN27w3NoMWQa9Lo9SHaz6zHgnhTveTFQ/oW142T1n3wEzE4qKAurT2hOix61b2uUO0wP9R95b2v0ryLxLRjmI2KX2pp08UBa9VAgAh22fI1KhK38LVigCE+0doPUcEnF0FeogihMdtC9ZFRP13az2+sfw/p+STwbCpujEdfg4qpDLRAkE0saEUVBBpzSA4ipunEm++PrsqDv2jXlMGeP+ViUD+m/BcW5von+M/d3q9JsjT7hzak",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2018-08-23 13:00:52",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "DPD",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "15504": {
                            "id": 15504,
                            "externalId": "15504",
                            "application": "OrderHub",
                            "channel": "royal-mail-intersoft-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Royal Mail OBA",
                            "credentials": "cbwMXR2z3oeu91Jcqi8PbLZwsbe6mRSmKotJNhSP740WFyH/xN+I8AzruE/RgDS0urTeMdRLX/EaaeVaVszk/SnJVyih4VJyOmskqrzzGVrA5iaPGM1nLcY4rwzZZd9DFNLYqEoSbqgJ141orsmCLVL2ApOLY7SpfePZywCmFnKlTJ3FDsSLix4kX0wjuIWi3QJYAMFahOrb6RXR24BVijZ/x9mniiKUBkzJZccDuvGcpRLcNWgnl//d/Hspnum7fmCjhcVZpeKcTjyCPlNKyrbwqtYYCyiADvh4zzQt1rFPR4DlSE6z87M85yPTiAr/1UWTohXOrE/vOPSZmxw5cOg6Y6weDvi2GDtu4CZvR2+Q3k89NyyAIT/VLGMugWmJKVf+aibk6dpd5E4URa23CA==",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2019-04-18 09:29:14",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "Royal Mail OBA (In)",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        }
                    },
                    "stockModeDefault": "all",
                    "stockLevelDefault": null,
                    "lowStockThresholdDefault": {
                        "toggle": true,
                        "value": 5
                    },
                    "stockModeDesc": null,
                    "stockModeOptions": [
                        {
                            "value": "null",
                            "title": "Default (List all)",
                            "selected": true
                        },
                        {
                            "value": "all",
                            "title": "List all"
                        },
                        {
                            "value": "max",
                            "title": "List up to a maximum of"
                        },
                        {
                            "value": "fixed",
                            "title": "Fix the level at"
                        }
                    ],
                    "taxRates": {
                        "GB": {
                            "GB1": {
                                "name": "Standard",
                                "rate": 20,
                                "selected": true
                            },
                            "GB2": {
                                "name": "Reduced",
                                "rate": 5
                            },
                            "GB3": {
                                "name": "Zero",
                                "rate": 0
                            }
                        }
                    },
                    "variationCount": 0,
                    "variationIds": [],
                    "stock": {
                        "id": 6945863,
                        "organisationUnitId": 10558,
                        "sku": "EXRED",
                        "stockMode": null,
                        "stockLevel": null,
                        "includePurchaseOrders": false,
                        "includePurchaseOrdersUseDefault": true,
                        "lowStockThresholdOn": "default",
                        "lowStockThresholdValue": null,
                        "lowStockThresholdTriggered": true,
                        "locations": [
                            {
                                "id": "6945863-464",
                                "locationId": 464,
                                "stockId": 6945863,
                                "onHand": 2,
                                "allocated": 1,
                                "onPurchaseOrder": 0,
                                "eTag": null
                            }
                        ]
                    },
                    "details": {
                        "id": 1888934,
                        "sku": "EXRED",
                        "weight": 0,
                        "width": 0,
                        "height": 0,
                        "length": 0,
                        "price": null,
                        "description": null,
                        "condition": "New",
                        "brand": null,
                        "mpn": null,
                        "ean": null,
                        "upc": null,
                        "isbn": null,
                        "barcodeNotApplicable": false,
                        "cost": "0.00"
                    },
                    "linkStatus": "finishedFetching"
                },
                {
                    "id": 11400134,
                    "organisationUnitId": 10558,
                    "sku": "EXBLU",
                    "name": "",
                    "deleted": false,
                    "parentProductId": 11400129,
                    "attributeNames": [],
                    "attributeValues": {
                        "Colour": "Blue"
                    },
                    "imageIds": [
                        {
                            "id": 13812565,
                            "order": 0
                        }
                    ],
                    "listingImageIds": [
                        {
                            "id": 13812565,
                            "listingId": 10222599,
                            "order": 0
                        }
                    ],
                    "taxRateIds": [],
                    "cgCreationDate": "2019-05-03 09:28:15",
                    "pickingLocations": [],
                    "eTag": "5e0eefba90b8832702f523e2273fad9394aec07e",
                    "images": [
                        {
                            "id": 13812565,
                            "organisationUnitId": 10558,
                            "url": "https://channelgrabber.23.ekm.shop/ekmps/shops/channelgrabber/images/excalibur-stone-not-supplied-103-p.jpeg"
                        }
                    ],
                    "listings": {
                        "10222599": {
                            "id": 10222599,
                            "organisationUnitId": 10558,
                            "productIds": [
                                11400129,
                                11400132,
                                11400134,
                                11409247
                            ],
                            "externalId": "103",
                            "channel": "ekm",
                            "status": "active",
                            "name": "Excalibur (stone not supplied)",
                            "description": "Wielded by King Arthur!*<br /><br /><br /><br />* we think",
                            "price": "2.0000",
                            "cost": null,
                            "condition": "New",
                            "accountId": 3086,
                            "marketplace": "",
                            "productSkus": {
                                "11400129": "",
                                "11400132": "EXRED",
                                "11400134": "EXBLU",
                                "11409247": "EXWHI"
                            },
                            "replacedById": null,
                            "skuExternalIdMap": [],
                            "lastModified": null,
                            "url": "https://23.ekm.net/ekmps/shops/channelgrabber/index.asp?function=DISPLAYPRODUCT&productid=103",
                            "message": ""
                        }
                    },
                    "listingsPerAccount": {
                        "3086": [
                            10222599
                        ]
                    },
                    "activeSalesAccounts": {
                        "3243": {
                            "id": 3243,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "amazon",
                            "organisationUnitId": 10949,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Amazon EU",
                            "credentials": "7KcoJhcMl9cT5rTLVAL58CRe2hSr87TkfK9zdFb2urppxk7EPnDoisnizeXmEKMFeYlaBwVoYJG/J8rIB+MwSKJkisYeQ3Az/egy0uwOs3UE7Z/UunXuA5xMRIZLI0GkzHYH0DW/67U6/zrSNdNuJ+QXgQLiKIVp93xExHup3yGD0YLHvxNSw58/RoWrRLpL+TXpT6X9VHfigokGHrIu4uwRnyByUyQHaAyeUGBxItx/pbeldHE1T3LJoY8JiGkoQwIUYCiE4jqoLHCwXqzZ9pWQG5LFNiSaGkA7WmOmTsWZbymL7Mg/YA7DzyjuFXS1mfdDFCRN512M1fshjF+X4psCnutqb+k8UgBvEHGedBJtQrfecrFRmkovlWD0Po62JCzpJAGCKIQ6IZqioKZQLEhD4J5AMcmx+CiVHku52/5WvByALEWvk20Mu4nthBHMnrc8whDd1HNzL+Idj6r6PaebVX22E1mb8w7pUUcu20lA9LNwcBoT4VDHHXtO5nPwD4vsPAUykf5es2vuEHZfhXDN4kdJ2s0ZS85dqxK53Qc8IsI19Bifb0Z4ofOFhFApFJ+8Bn6/Dcm8KahJzH76F2cj2bSevXflDLtlbSzQjILyYP9UifJncZLriu6mHKv+b4oshBCzIl03MdWXgoLUrHhgOiGB1jctzTp92TYX0iQwomQDHudS56ZaGGDBMlJtED3F6ojBhNBZQy+vIeBTcbIBgs60H7PgAjbPACtt/nN4zlw/eOFQfUEyWW3g9lqGPaIGwZJFUZeFC1F+DmfyReyoOqOaHhe9HRYWxFVx/QDjTZReHTHtSFIsAVX7HH8lyH8XnJqVKqdIztJWmikcoyj4Djj/LtcOFlktZFRHtZZFZW9i8/gKWj5LuA0QhlwgteoF0cY8KNMqYhpdghRggN3mU66tiXl0hSHLbLsPSORRdJgi4esMT7jXzvFVXhllCqycx0EkrIbnz2HTndq9b6lAdDTxxLIEPcdObHnOAPLPWxlmrxzxxC6922DqkqlK9bmkKtrTHDuN1DrGP2p+Lf3mdrTGoskYwVJ2x89mR0QnnQtQM3jjXst/ujwZfRKtEOMNx1Cwq3uqOSsCTt434A==",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "sales",
                                "shipping"
                            ],
                            "cgCreationDate": "2017-07-11 10:47:32",
                            "stockManagement": 0,
                            "externalData": {
                                "fbaOrderImport": 0,
                                "hash": "3e474f1d119d973829736ed9f7e18a699ff607bf",
                                "originalEmailAddress": "",
                                "fulfillmentLatency": 2,
                                "mcfEnabled": 1,
                                "messagingSetUp": 0,
                                "includeFbaStock": 0,
                                "stockFromFbaLocationId": 2796,
                                "regionCode": null,
                                "marketplaceIds": "A13V1IB3VIYZZH,A1F83G8C2ARO7P,A1PA6795UKMFR9,A1RKKUPIHCS9HS,A1ZFFQZ3HTUKT9,A38D8NSA03LJTC,A62U237T8HV6N,AFQLKURYRPEL8,APJ6JRA9NG5V4,AZMDEXL2RVFNN"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1563850808,
                                "processed": 49,
                                "total": 45,
                                "lastCompletedDate": "2019-07-23 03:36:47"
                            }
                        },
                        "12354": {
                            "id": 12354,
                            "externalId": "47fwg8cpdt",
                            "application": "OrderHub",
                            "channel": "big-commerce",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "BigCommerce",
                            "credentials": "nYg4OGzKeMmDeVdlbKJRBg3WLTlxIWOK0or+WcNloytYBe91o1iu4DDfhTuCiat72+b+3G8yCA32SCXdB2+mzBxnW2mkSfUq73gHOKhQpNkBiA5dT+XYfHokcdHJErxWps+QtofTX0zFmINbvZOvmAvPpLdUblNMW6DI7IJ2wHoGFlo0UjB4b1PIZJ3HbgDZRD3RQWCIHbXgxpuOAKAJx6dHVtnx6hxqVGMrXB2CApU=",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": "2018-07-30 15:12:37",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2018-02-19 11:20:51",
                            "stockManagement": 0,
                            "externalData": {
                                "secureUrl": "https://store-47fwg8cpdt.mybigcommerce.com",
                                "weightUnits": "kg",
                                "dimensionUnits": "Centimeters"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1521127671,
                                "processed": 13,
                                "total": 13,
                                "lastCompletedDate": "2018-03-15 15:28:15"
                            }
                        }
                    },
                    "accounts": {
                        "844": {
                            "id": 844,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "ebay",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Calico Trading",
                            "credentials": "iCpnvOwePsMJq7J40bqlO77erZ5X+00dzKvuRk4PpSGCEsEYCixvrALXTh8lZ4anCsePIJMLRFc8MN0C2DNL7WWBffE20qfU4ZmfY6BtJjLVpXn3Y8/aLas6uI7BYX+xydtAavkSmiGJOLPEtQZqCpHT008zPFTA59ebB4tDe2DvZHIZAPoxMX+QfpaaujyBxpzw3RGmId4C6LzUJ2G5meV8tzw92/SMU5alnWCrX+p1LUK3tk7CJRFDU6PSOn8Lh8ZegQEAoMUGMEOCZuIvhopDmYiCm2PLvk1f+IofZXTufQtAjZBS5yyDTVqqKSS056zp02tyh3J0aATDFpVONkJ3IaTFRNpH0eG3nwwsI0RgaRPTNVr/c2Nhf/KblTE0P8iOus8UJZTIesgXQApt2yvUr/P/X/VD0gkXZO/nREmdRqAerC1Usx5mCLvAUBYoNo3el8jsdYFX2ykzbwFd0cHJGaQPujEdjmR4ELs/llTelUGT6v+MIrfw9cZQ8SrP2OziAP5lsrr9tqi9xG45dGas+/jCOWdU8eAxm5rcQEtDlWG1Kk74tbwWFLqMgrKIVE+yx5Xtud+cKgEp2IDD+4bc/7plEJBW0XQ6nMJPelfKq4DnQe4vw0hcgyJjAzJFyDQtN0xVlOmciVHRi44PTgEFKTVUmwBzwvxsNeUR1an5qeZ67gOxRHgndI0QVq3aKI8vm8+1arW1Hg7iYYbdoZ0L+Inl+SGRdQFVwfvgmLjV9YacJV4o/m2X/RUawj7i386r1HSitafwnICDgsOk/psvSb7phj4Z/2jxx+E5VjlW38v6bVpk6UYuGimbVyh9gqNGq3oX1rRPG7jAiUQTGIoSFt56BJFAEyDMXqNnzv3c/nYm+gTw40pmhPPAUMb30ZMecHdIG5ulqgaQaxADOM3Lc4VddBhFO9ejdIrACf+Az+TI4dzGgDnh/62yrS6hkdP5qR3N1LUQmyEgDH386oy7uQsoe57Dnuy29YNI9ijjC/3Zlf0k/O0SzqFCKGDOTOWPDA8yj5bw4ZnwyWE0Sl7FF3QshrhdmMlJ8hZz6oE8M3J8ynNPNzHl4k+ItplWSp+tnOgMv8r6CZ3/jvW1vfUQ1z2kzw7g8dt6NcQjFTbCAriDdhJPYTgeOtDRwaWpHuFrezA0suhYNVo/7CUyGzkOk1XFpMazNlBUKDFHFwGAHLMRLTKivg0r/8pQzoqROxUjDedGs8YXQNRAkQzdQx2cTEwW4yJNrEn9j8nFD+84l5j+xKTQfwkbfQ0AzVBO/psTYA4PAZDArtxqxiTroiMNdaZ3P8vXDpojkardR2QKsQEEoInXaGHpNzxLVdnrZcbRBCZMaWacecUH6H7vE41PAnslbm6E/0h1gCHK2tqYCLH1M/iYTL/hp64nPlPyCb3P0/TGu/gFcamxSRqPF4cP/MnENAtgIW9UxRsEEUbMVSvYxg9MtkADggF9pmL2L4Crkj+FbTZ7+yhRxhU2ycwbhZzoEXDOqPauxnDEXIbXlV0gJrUnhwIcA0NQi5JkyZukM3HjvWX4j/MB1mFsKlA0wdfVYmh8kIFr6bLCfjuipbC/sUIB/93U+rvSGiaVNqM52w6dJjIQZ+p9eDJzKyHy5JIipPRhCcMpBx5xnUA9rlwhOhy9wKzxRfUQApXOPu2MavivSO/8cP5mLdkylbH3T1vBBcuSVcHhQ+Wvhpd4R1zIAt8EtZyfSJgsiw3EsQHXfebAoKffXQNKX63T2bXJi4WAOrRYjPAsey+YmHk=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": "2017-07-20 12:16:35",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2016-01-27 12:16:36",
                            "stockManagement": 0,
                            "externalData": {
                                "importEbayEmails": 0,
                                "globalShippingProgram": 0,
                                "listingLocation": null,
                                "listingCurrency": null,
                                "paypalEmail": null,
                                "listingDuration": null,
                                "listingDispatchTime": null,
                                "listingPaymentMethods": [],
                                "oAuthExpiryDate": null
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": true,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            },
                            "listingsAuthActive": false,
                            "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=ODQ0",
                            "siteId": 3
                        },
                        "1096": {
                            "id": 1096,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "royal-mail",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Royal Mail PPI",
                            "credentials": "Royal Mail",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-05-25 09:55:08",
                            "stockManagement": 0,
                            "externalData": {
                                "PPINumber": "HQ12345"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1445": {
                            "id": 1445,
                            "externalId": "1445",
                            "application": "OrderHub",
                            "channel": "parcelforce-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Parcelforce",
                            "credentials": "2+p/GVQs1ndg/heKwGT6bePmrr9ElapPzIhmSYdggFDPypxFY+/sIyYl5nWNhBpugPdB/rWFnyon41Trir9I1tPLadwkG3tx4nXqeN1Fs417/NKHRZtZw2pYcLAOYiJO5egBD/wtYAVOWwTie99HiBsOXxjuOifLQ3/eoo2lgorjmnQeRJ5sKY535YOsHS3m0F47C2ypo5emUIw3pXCoSncxdDydOmrY0H5tJLUIA9nGZ7DDuNBQyfFuu97XsIExuriMw3qIg9MXPcAFy56silpxXdE8qMAlIN9NNJQqlcSOt++u6XpoeO6FEHXmvc/186H3Pi/XXwp/xpr7+0Y8FK6K0/rPga17hGWRLY+AidVnNyYl7qc1LljcEmhSXD58fpzMIOcH6XRjiV/giHHZ4EqTKBMIBpxwJ8fpqpJAGAlGs7t05vol/44LQ37cVzNp",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-08-30 14:27:11",
                            "stockManagement": 0,
                            "externalData": {
                                "config": "{\"emailNotification\":\"1\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"1\",\"sms_pre_delivery\":\"0\"}"
                            },
                            "displayChannel": "Parcelforce",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1447": {
                            "id": 1447,
                            "externalId": "1447",
                            "application": "OrderHub",
                            "channel": "dpd-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "DPD",
                            "credentials": "ljkIEIyzleoeSE6GrLzXJXh9nlRkVmiWu+tEm023Qsld2iu0461qm3LK9ZmwxZ110Jh/PBp8E1hUuUd61B/7cei8QWZcF8qjAq6IyZnkL+MygqJrScSdbowuiFSJfsw2oKiNH5pkLZ37HMyi/s4bNkCTOCzNIF+QBeWDX7GEXwXAkBhMGUIrQcXrjvf/aJV6+9D2Wv3TZqXRrZHg8HYqL7KJm1f9FGQ5H6Fxsn5Ams7+qTcTfV4nxKB7mM2aQxLbPF2rz0B5UU4kKQgLjc6p6ISTm+HRkEPqo+TQMZU9diBQOlrEm5MPBDK/y/QKZf8SqtEG7L3VKSw5pbpyThRUvcEeWsq5eW+r3zQ1bhzOewYHHD3psQWUlWlWC2+ERO430xrYDiihs5gOBhtG5rYI15g5Hz7GrRSPXTJl2KHeOrwTUnKVdmgOTYFBNwiXB9yHAMw79394xLhEpgeoZAon59z+n/kgCV+xf3164Up2DNB4ZXeC0bKCwZS5UU1aqGV8imcBrsh45MlaF/jDeRI+ZoWhOUjGdJZrqibPhAKnOG0PW4028tQ7WUwl1Q8qZ10AQRqQMTIChoiTVr/CYJ+P+fW0redHDDXzi2jSa4sp9sPnsmkCIP0wuOkZU3yxawpi",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-08-31 10:08:30",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "DPD",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1448": {
                            "id": 1448,
                            "externalId": "1448",
                            "application": "OrderHub",
                            "channel": "interlink-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Interlink",
                            "credentials": "NhqQH5Yvo2mTPMAitLN8XOZRGJjNKbu4ld0zvfLH195fntGbuiTG++69OzEB8uB0xUkNuWl26t4ou+Xl3A3pG8Nj4doKnuE7Tnztrn82wVrGMkJHElVGs06ffZdFvG0s2MKehldhazxC4ycuEbjDX/AQZkOTULoat+XeDeujvZnN2xxB+o7xpx5FOjeJTyyypjoFa8MEtGQovHpCPYO7ph/Av7MU15q9doddvrARtiIEK987xXMSnei8Q+eauGWUs+74javCusSj0u5QKPLOoph/iUNtsU6XPuMgSbUvaNpQBIR4HVo/ztnXlOx8JeNC+TrnBQX13N+5I267uYhMNpZyh3I4jj2IE44WeJvWCCBCw+68U3UR4DMccBKx5ClJkReekIwl9D9KNO+dK1lEjL62B9peb1JQ+RgFeulo1XB4otF/cJXD9MeliZVDF8P2rR3v7QhyHfpMPQdOu8w2/blnjpu1PUdUPQhUVACqPNJjYpLLSeSWZjZaTENJs/lTTmOSUAMYMsVwCNAWQL8zpgxNvK3PmtStI9g4uNhRPUUgt1d+L+Pu/wSqkVhqQ24YbahGfPHKCC09QODqBBkgaHk0IlcVIsKLOJ5efJBCP79HOpeN5ZvZpBhhni+yAhDxeRlk996cQJGl85xiVHGgU6Tf1KycBa+SWeKj+y90s1aKVU5yLhEJL+DNeq4vXHWMt5KvQoA2si8GMUoKzDnP1w==",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-08-31 14:28:29",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "Interlink",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1456": {
                            "id": 1456,
                            "externalId": "1456",
                            "application": "OrderHub",
                            "channel": "parcelforce-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Parcelforce",
                            "credentials": "X5d7L4B6DUXntVIsKEC6J7ULjviSYN9GsxICofFbFW6PswrPlEmAdeK7IU7ZjFrFRTPaP7W6e/Iz+jG+KqKQNCLVF+B2ggau5v22zwx4KGTl1+9TYhkfhGHXhA95m2l5sVBSNOiSr9ly/kprrBXa7l22ouWiXYXt2Fzcx2VbDjYd4zAIN1Tp5N80alyfkRzVM/RoQJ9IwFVoFHqMXE2FVPUz5VAriZ9LM5DTJHUYuS2bZ8d+s8c4BOzrwi7NZhEzzsbWtDF9gKNRKc/wqKW3idSMPGvSJTnjCGMH9+7FxHXhYN9BE/igqnluhIxUHttJ7A4FQw3yEypyDDybfJzta54pGULumsMmqkBSOZ69YgKCrYpgxfZdhfnzmy8hIiAwoTOZVsgQbBP4rcbFyyD/O+pXGuVh3IDeclenPbv3i0jMu0SsVFDwI5QcDoostNQMbhCe/+nuTvREI1p86aJyAA==",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-01 11:32:39",
                            "stockManagement": 0,
                            "externalData": {
                                "config": "{\"emailNotification\":\"1\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"1\",\"sms_pre_delivery\":\"0\"}"
                            },
                            "displayChannel": "Parcelforce",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1457": {
                            "id": 1457,
                            "externalId": "1457",
                            "application": "OrderHub",
                            "channel": "dpd-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "DPD",
                            "credentials": "fG7mb7o2273LKUcn60vpLqfgxMp0G7LArSWNJBhjacburSEe4dIIXIwv33UPngs02UrfSOtf6YxYLQ6+efXNa6NbF4r1WlcVlbsQq4kBrkPtHWnfJE/IUgj6FNC0p4vqMB/3bwaV6f/gJgkSeTMmTGnRtr36icakeFbgOG+n4mBJhMpH+CMErlhJnO3+7Kq7PoAaA/1EZyHSf5hMBnrU4ZBrFEaGChToDRaiZGPgAiFWs02BlzVXAFLQou3FD+UauH+zbW1kRXCd+OOYTG/ew4yPNPB8SC3CCHwci5QiESVIs+q/qCApLMBVPVq6/EA8bghNsO7VllIRhUqNaHC/X+K9IePaplS38FV7nNd8twLayj0Fv7JSNqD8BwgVWM+p5geadxX9T05fQ5ijqfCP3qablNY1hJWDQMnxbvhExxjSO0BPvaafYOHE/HiokdsCDjLiiBCa4q48O/tiLMgaR0kjpFmD8xcmZj5+fPKTCXKd6jssI9pTEtoon9dQhCo0S/kF174ke7r6vj/9lKr2rTdVGlhNoqhhxNet3AeXppMk7PZ2JxpiYFQIy3CTuCs6Cce4c3Gdn1Ws/iSZi/9PpMhP/hvUxYDO6SMN5AmI7S0=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-01 11:51:43",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "DPD",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1458": {
                            "id": 1458,
                            "externalId": "1458",
                            "application": "OrderHub",
                            "channel": "interlink-ca",
                            "organisationUnitId": 10949,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Interlink",
                            "credentials": "xt5MOjdt8njG8ACdxh7Bj0wTCGxMqU0wJXIoG8bM/JuUSSpCM7P+/P7OhAjROM1jnbeSBpT9UDmfgc23CaR2kW/ebcVqrRMwWsxDoC0yzR/adLgTn+TnV7JqGWYV2Te06IV9otvmWG30mOSvrawVTXM956dun/Al/hUAM2E8CJFFnG9nG11DKXfa7CB9X9PeCGGHq+YRuK/n7xI+s7WblT+BU1YSIyhGiSvzKCYIrNtNwDjq7m8RqDsCtYNGAUAufF2pACZKU5L/YF7ClH+5pzwAFalqepI6GjrnMkO5gIMHA1qpoiYBDlSdctRGIeteCz52n4vvlwHAhWQKX/URUiRm8JYUdwCKcRvKw7SuYm4DN4nEnjA8oVNOA0zvMMtapgvuHQDX10NJ3Zwahh8BLQo2XvjFfi8uHkJRYP4OqbCULWard/8jBosdZOPtXJFXF0ZGXuxQEm/vjNOfz2wOlhXAK8ppZsm3YV3xDv8cIglkWExxs9z20i0IBQYjON6xmJLymqwqBmWFo9AK8KPZ15pmOoWHOgAcfpKduqUbNoTNEfnLkDz+eYnpXvWRj4jy5myjKUoi/QaLBBBK0G+bH+61cAgsW8bwaI4Wl1+Hqc8OlWc7a+mrmPpSNj7291kr3zO8oM+C1SASiXjo0oqTfiGiC9jeMSsLRiTz70gLn83dqaLkwbtvFsI9z1JPVA27",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-01 12:01:39",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "Interlink",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1517": {
                            "id": 1517,
                            "externalId": "1517",
                            "application": "OrderHub",
                            "channel": "myhermes-ca",
                            "organisationUnitId": 10949,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "MyHermes",
                            "credentials": "h1S6HNiK87jLSSQ9mQdgnFR3kwFgymBbpYfKOKTOqA6W2GDiHqs4GRc8HFPlDPA4quy31sQY+v7aVieuGjrewV0mV7rExhiVhpnPE2e6YOr1OVk2FS3VVfVSKKhcMy4RVBppxlE1hPgW+Mwe8WHtW10AFemp62BcTQNXsSIzfMwNJVjYpm3yZklFUMWUiUMqJAsyi7QkZUKVOY/z36k0FVgYPMjeq+WdaUm4T8jvmVXJtLJheQjpiYD8C8vFutCMC5JwCEAOJp0EPUiniz+FhkOI7b3s1U0wUt8R/aI4VD+R4JnHhohsCJHfupcz9xMQbc+3FeociXqZJJ8JLHZSRHE1g9FcBCzHHAMaFT8z3Tc=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-19 14:43:13",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "MyHermes",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1518": {
                            "id": 1518,
                            "externalId": "1518",
                            "application": "OrderHub",
                            "channel": "myhermes-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "MyHermes",
                            "credentials": "KhhFj5g00yulMijSIH+AIhJDZOKhkCOvK8bmDmK5ZiL08EdLU1JvIkoZdb+/nLDwuv78t6Mkh7zjHnj2QyrDzVBYmYp1gIP2otSCu84PvwFEOgRIEGKXIp18kwYHMBkhE0HryaoBVwYnqORH5/vhVz2rmUl3q33+6F9oeKIEGziK5vqf8TjDXJklCGCahkQe+zjY1cPzQc43pLaTI8meQ6i5Fc2NtMglKrStfE3sysmOH8Qw0aNHzDs0R6egbZvvxbvcYDl3bqk6qpllOE6dqUTYu5OSkYXN5ckY2BzyuyjgpF5Qbt0ytCFp5WhngpdcsAzPBSJsbxLi45+KvUcnBCrtlxCbS+0kzxyj470rTR4=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-19 14:48:47",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "MyHermes",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1519": {
                            "id": 1519,
                            "externalId": "1519",
                            "application": "OrderHub",
                            "channel": "myhermes-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "MyHermes",
                            "credentials": "9ss6kvrwHKcLDKIZomeQeosQVceVYY83oaK2gEN9EWvxyZBgc36LXjeFXjzCqNdZgPJova1NC8lsOmxLEQ6rQkqJQ2ORcwP61RON3qtTVmOtbRodGWI8F6Tif5l4JVwN3V6U0eYv0HeJIAZS5P+y+qiEWNteN3oMI5s6O2Z5ROpFJm4Wmtr+mWhJstqHXxzfVowEr0jgzVV9ovv/I3ovHrm2oR18pJpQ8F9hmbKlWS8Mx/tsuprfKDXHB6yY0TtY1A9rVP/yVR6idBExH0WovwBSiWH/w55ZoqTdrNMvlnIIdQ0VBFezjqlg26DQQsvfnbWToT771KD0iskq+2HTY2vwjQndkoFFQXO0aX0Gr9E=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-19 14:49:22",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "MyHermes",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1520": {
                            "id": 1520,
                            "externalId": "1520",
                            "application": "OrderHub",
                            "channel": "myhermes-ca",
                            "organisationUnitId": 10949,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "MyHermes",
                            "credentials": "CktJet0qBgjEGaDOHPUDWB6KCpZAtwnby4eUv3mGBDbA/xa91TRzfWZp1W4R16wOwB/A5EXvYZMpxQZg4XtdRDnfapKH+QpL6zD3ppFnXlwU51dBEq/X3ulpR2VUSPoxzaaKsFW7BsopLwlNMkBg6XKPiy/VBawcdGocWFgZUEuptaBhywgJaX34BV5ozh3aECMrB7P7zHfG2awMDizXerCg2zjeiSr4oTiL1ohbMMMYoA+dr5JIWrCpk+KIUSymEkjgeHS1eOSSr/XqoaZ8RrB45XVYFzIlOXEsGydlGA3VTVhCNE6E6AsLmO0pWopCSx0aQDc7oUk04KDDFaGSg/i3aFMIxxL2s1RzO+ucQ0s=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-19 14:53:24",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "MyHermes",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1822": {
                            "id": 1822,
                            "externalId": "1822",
                            "application": "OrderHub",
                            "channel": "parcelforce-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Parcelforce",
                            "credentials": "S4gu60+S+paiPJMW7SRwvl1pT6QI2trrbjLFXSU0W52/RtzGLT10+TbGnh65r1EL8/imaGzb67YQgbU/zDCE3v763VAP7gKfrq6ifPlHzluRaXQteGpmKzQQWPp39q6XgTzzAANLI2otAzTrQjZXYY9fUCaYdGyR2QmxOdhfZlbJQBq7cmvOHk08fPy+3DYc9sIGnOXLLpdS1rJ2apJWY03oS6d9DLwXRvfKPrwHW8mofDFl+WK4gZyRUcrlLTp2v2HrzDw9TPkqs7chL+COpbscgph4soytOYCrl/Tq2gAqjVjoC4xaUCzrbZ1RY8U/GpxFcwvJW0Gi6ZgU+4UEYLFeGa5He61pExi+bmwp2Wbase4DJjfipO4anqqwySM4iC/xjKJMg7mD8CLWoLHqzsYw4ZvrM7tg4pQo8tBVDlidp7S+DVg6nDMgogppJ4XbOLW+/62n18TvD1DJNSGLDQ==",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-11-16 15:41:22",
                            "stockManagement": 0,
                            "externalData": {
                                "config": "{\"emailNotification\":\"0\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"0\",\"sms_pre_delivery\":\"0\"}"
                            },
                            "displayChannel": "Parcelforce",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1823": {
                            "id": 1823,
                            "externalId": "2015291000",
                            "application": "OrderHub",
                            "channel": "royal-mail-nd",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "CG OBA",
                            "credentials": "hCrj92WJJBP/ZwLM2YAsOUQpIJgNNHR1miGevkZ+ojdpUQaP02BEdQnku/fS+aZPgK+iCw4eoJuAD+2qY+FuIL3BC2jlfZCvlJ77R+bke8Mpp3/iUSFCenaWyxSbCpId4AsgilA75jx6vp9iJb/JfJ4E4ptVX+xXKkbasftz6zahmX9ZPGyQ5xF5EuPoT5jIPi+1Nrn7NwczmdUgFXYELPjeVCV3Mu4+Fktfd5u15sL0IN8N221wSo/iXsdEb/JkXxwiyyNaUJpplsdrRTF0tMRaHj5iz8NbxjzL/q7DZh5E8zHHGcCbWoQ5ZdRaUWEA8W6qF3Snxk/Q7KgHdmFYPMl82/MFAuqVgJN8JDVKSGpoxdB6Hew4iC1cibJOJYJbyTS/j8VBiAOex5jLRjwGpiX8cK+7tsWRdhcP27uX8SgZqvvBImyH9kp901/V5HYFiDGVJtd8j8zNpTVngEJ9szBrFVKQSrOvob4ZBCOLASpNP47CrYYmcYXcuO0hy1jGvSL7FFXZaYGUXFcZsZ84SxpgZR5GjwPqF0MctEs85xw=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-11-16 15:45:10",
                            "stockManagement": 0,
                            "externalData": {
                                "accountType": "both",
                                "formSubmissionDate": "2016-11-16 15:45:11",
                                "domesticServices": "",
                                "internationalServices": ""
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "3086": {
                            "id": 3086,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "ekm",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "EKM",
                            "credentials": "gwdBr3TbEXYietikBI+mOpc/BS8iOF8h5kCjQkIjHJQteqPeVH7Kpb2PHH8gA4bUD+nu2YnmfNKW8BNqRfvrwxX0jGIXBEBpk/BXbyvuu0KuYyoguq6K3iTbaM7awC2acUBeK5SpaRSnGYB3zODVtFY/6neMK9b5fQOhyWm2itMphSkEicN/9g6z8/Q3myo/eT7Wj1yf2SaeyA1zrp+MwzrbiVt5/800uYARkIvqqu1dYQdKpKcuHH3a5GA6MLupbPB/CPHldaWGnv2kIdNWiWz/6SVSJYI7jmru2Qnvt/mdHmFHjXXOvNl0b/bZoQaEYm9xwCPC6+14hS4bsFnFqBqoaDnl8/1PmPXoOofQ9WQI6Tuhncu0xVJIONdIN6zhIpCtKK0KylBQ5OBnusHFDUhy3F5WFX+n3K6+WbVWbNWOCqmDOdePsCWM9pTBFvpPJkmHeDeuKfu21by9Gpc1KnnwKdUcmWX+X+8kO2m0mgs9xOrlJ7+WC61TQe93w5/QIIPRmC+CjVhOawZwg16M9U89k26aQMoEWAr5PA/MLIlElw/mVdlNwVYig18fh9hPBlJMcpHh5YTFUosyj1pP7fcDImmodxyoH5GbF2elwB11sfNyALsNFvz7mDBJt9Bec3piOaS3mCGGkwbbuUMSWhbpX6Gd/7hC/ZCp0lvon2AXt/pNAPKZYsPURnShF/D/SbVMF9qDFO0Fd6o8dwsUAPFPIBvDl8MfBrqs5VKa07BqorFZ49QiOuR5jTKDAWpY",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": "2019-05-31 22:02:04",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2017-06-26 15:12:05",
                            "stockManagement": 0,
                            "externalData": {
                                "ekmUsername": "channelgrabber"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1560910068,
                                "processed": 392,
                                "total": 392,
                                "lastCompletedDate": "2019-06-19 02:13:15"
                            }
                        },
                        "3169": {
                            "id": 3169,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "ebay",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "chosen12010",
                            "credentials": "UA2QeZxpw9ep8cbz5gSaMPOI2S682+fB8XnO7q4128yBxcp2d6tlGt2M63SuwTLOnC+IZv8K4nWetiE4o5APCqm997qiPh1GnrNxcwA5b5uhhDOz2jFcSuJ/F6Yl4QjDQLgPTGwTuoVnXob88cO2d1SLEpHkGjXEtR2XOMi9RNW85FyfQpUtzdO9GmSaExjNiGhPGYeI584qaMJxCbAQ+CPLq8i+9Hyg1L7lwdPONSVsDGruBrDX3JeAXdSEV+MD34HWzyAWqqU2NePseh/zr9ZVLBzXj4tKq7su0yrpbq6knM8mCzqZKom3zxeC6rw7R5RCHDrmP1HkzvS0PpGjWz3kx6/9GJEVIMMWJ3JqglQDiWoBalXT41lbtxFOuI3140msja1qdmavPesf5ZAwHq2ryCCtl7DlRLYb5m2EJyH/mPYS0XP+f2n+DgDNXtQOtclPyR/olO68VClw+AoQHSDnqyh3Zc4WjmaLhpsDUz/5PBwp+dm1NQhTlzZiEXk2RDCScILu2ZPddMOclnrn2a4QXwQGag8AMlq7p4sBMU9MLvO073YdrzcVvxNaXnxAoaIZ1WD+W6OdA4SNjdmlNcB2gR4pNjm88paG6kEd+SGGgTTVddA/fee5kS0OtI1S/ydgGqjLsPYIJE+kTcEFc1uAOkyRVMNTN3T2zwjeleRsLTZ4PkXABDokhmO/r0mo6dxjThId+xA7Sq6Jg7MP+Tu6WveS/UbjQrtC8NicbTgxPzu5Xa9rCcK+/HyC4zwdFaCvC3MQSaGkn9MvsRcVKQklQlOISZaILV10B3/4YM5JsDmDIZr8V2hYefC7JuvVXctaiGDRbsw7Ju58vTi65dNXA0myisoNDR0cai/EvNwHszYzaocCdX5af7NXaeCxX+yitu/J5EPMmDDEiFAND9Tsf1wf/bF83bpalEpKsAaSnbvn5RE+6M8xC+oiW+At8zBLEK8SZb4fzfI5sj96Eu1qmpHTYnAUxvCzcHZmnZAlfmji6t0EfxSe0NFulzHKPPcuoUzgFFofrBhDaDXBteqMFquufFm7+vAp4XsVKgA0yAVdfi2U6niUhhw3G4D5zDbqyoKAkNDS1gnVK42sAAInZQGimQo3xO+I3nNECsbg6eFSWXwHkfgAnAq+neVZjcYm6o+qWv8To/MSw6hWE8yJc94JDu4nGKUFLlv0xE4D31sozgIYDhLggqwTAYyVIzNguxEDBo2t3cCcI1UF/+dgKXrZ3wiV4YWZJJxz9MKcLFm80P/2RITcQN9W3eVDN9wX0XPJsMYNyJp3BTbCdifrOk4hoF87oI+IeM6369uLIq8LN0x7ZHM2+GrZfXh8hSBiQnV9H19JI32/45QXaR0TU1BGVWXENy++mudI8F1ear3PDtUvxIg1kM+qHNaGah6braiv6XkYRLlH6b9YYk0BPQjoCwxyQ4HIzs5XLhr4lRoXA1rxuNeMRwV5tT6gBhsELw4vdVzKDDwhBSPb0ei/cbqc3oj9iYppLI8pwGoCQn/vBqDJaRJHoJcL9ZwE4CXFqgegU3arIGXqSFvxqlXQX6Uu+da371pu5MftsABT9j1yJSrXvfGxQrISF5SIz8gzgSHa0o5rZweOaqeq613Gwvsi8lpdMwCUX/kTGWArxeVZjySm/g28fvUgztpZGkAKcCVnSH2bzvLcvaqP6X3ezUAdfG6y+Lv/dHz3ZUFBTRGU+UN3JYDcL88vCkKrrOrsiXSVlgpo8+chOtnKjxnJFYnIWvLj4+qCBcD1NPw=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": "2018-12-27 09:19:37",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2017-07-05 09:19:37",
                            "stockManagement": 0,
                            "externalData": {
                                "importEbayEmails": 1,
                                "globalShippingProgram": 0,
                                "listingLocation": null,
                                "listingCurrency": null,
                                "paypalEmail": null,
                                "listingDuration": null,
                                "listingDispatchTime": null,
                                "listingPaymentMethods": [],
                                "oAuthExpiryDate": null
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            },
                            "listingsAuthActive": false,
                            "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=MzE2OQ==",
                            "siteId": 3
                        },
                        "3170": {
                            "id": 3170,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "ebay",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "eBay",
                            "credentials": "YnFTJPtcN2vdvLi3beaLxVmry6ZRq1g7FaltD6+W0fYrDGon1EY6sPOLqIjyUz7LfDd4GSfFV5X3svlWdNQyFNqWO/0nvF+Hte/I+wfv0C/mKOYW3yQ2cfdiTqAcsTkAFZC7gmnS0f42KDKntDyqNqLYvfoMH60r1f5z7GFTlIeeJw0ewRw+Uw5TmveiAcb9Q6NPSycQNxnK7zeAOUvZZ4bsIVWdEwS6wX7K7oP/zjMlSdPau7+E6BjrqkuyfNIDS8Tn9xKvwmigNn2yu1tp8WbrvXXonuAlBYZcmVX1nXwdvw9sMyRQbV1Zm8HB/tR9DloBjadUybCl/dlWFWovHd6xA0d3vgXLDOVF5LpBPLFlOmaAKp24f4Aw35vR8qMW03A6+s8jJvdSBkepwrvlNTiK3RfAx7np3Z9aSBA6P2BVpUrXuUvVwFI30Ub7jLBmIjIIyTsHOIpiM+XPBrQv3g6sdm2+oPh/5k1F+M/6ZAM5Pyml+lgnqEiGdF54EXT1cZiosVxcThC8Z5cg2XmbdU2ZHqQwhArbzZ59ne1moullk19yGywWK3JVeGhy87CHqyyJGZeo1MB/DAikuW0t1Aozage5nhGfkiBzcsrRT29PVyFMGKMWCqNLJQ56dXkllbwd6HuKsxZwGTwnaqJJRWE8bRpaytOjAs9TyUA8Ojo7/+Y4T7ozK3kbP7RMrPeLFFM4rBCCJrsjaDHM3IQOlAQr9AbpOwrb7faBtC22xdXLW3l+WDo+EpulNQ2gNdiyMO9pBinfARuW9UblAJRosEUsw/tgFM9rz55YwVFQpPaMFfJe2EVVWiXsbNlIowvvDARBu2CDm9Ti9my18LHYkLq66NnKiqSwiK1r9fT8jw3nb+UtdTvLXgRIeYCkGkxLcUUGiIdtcbPLdC6U89kmNjcnoTyl9gJQ1q1WzzVGI8FWIy/YLJBGTTRy6728mFnlWrPE6JyCDAidb4V4RE5BQNLFJIY/bICprRoLNUHrIbhjiujhcU/P12NpxtY00r+FdAJmxMO1LnPl2QnNsG7pfEu093Mof25j/NkT973TwdrO/yOsd8sR9KxhzmzKHGl5l3Z2QDyMO2Kc4/mxwUfm6J1Ns/Z3K9eWjLDntck3302oC1Hcm7sVTx8xJ35sTX8VzvBBdspWsavyDs1fCfvwSKhHK4R1zpTTh4a2ZEJV+M6BroxsELe93/3mwTKHhVKK3U+xsLx7LevcRdDpIo6rcP2wylQeyRXsw+d+tw6bb7RTHL3D7Mt5l3dWDpV3KyYGzEQZao+2lm224GvtZd15Ey1FCOBVi/ks0+VYK5bOaz/bPLNDVqCW1deOujg6V0kyHm6Iy0LROngS38G2ZooN8gEaXbUdE2muq7ORC4yXgs4diVPuQo63OubPHMLctFNU/LdgSjXGFyGbzc/TBMy8nxMsijPtVbBCt3A9oUFeIubRLlP3OkXmjPrnNiLQT3NUABFOrDiIH+6DA3fNu3+8o1JDqWcOKvQhRwsRXskuJ5WSpEW39vg8M8dO/F3V6uYe2ET9t2bQhis1CuZmEFo4EMghZZE4+6pco3v+wCTi4plbk/Hf0f9MNUHA8MiGMkCW+ZsNZX5mV24UGbGHcPG+D5LyNPIeUYJTto+yomDArATe+xY1m/PNPgftBqEngESjbv06xXwL4NI+74nch7KDFuWyLLGmZxsisd6u52jybidgkjJzUFrq4fPRvDt8P+XLBjloFlBovlaOjeQGEqJ+2nrBvGBFyvYGrUxiOmXab/6B1o514kFuqz3hQeu4UZjy8Wv/Or9vv2KobqNG/SQ+9Q==",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": "2019-06-10 13:30:29",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2017-07-05 09:22:16",
                            "stockManagement": 0,
                            "externalData": {
                                "importEbayEmails": 1,
                                "globalShippingProgram": 0,
                                "listingLocation": "Manchester",
                                "listingCurrency": null,
                                "paypalEmail": "accounts@channelgrabber.com",
                                "listingDuration": "GTC",
                                "listingDispatchTime": 1,
                                "listingPaymentMethods": [
                                    "PayPal"
                                ],
                                "oAuthExpiryDate": "2020-08-16 01:02:32"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1560132005,
                                "processed": 0,
                                "total": 0,
                                "lastCompletedDate": "2019-06-05 02:00:15"
                            },
                            "listingsAuthActive": false,
                            "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=MzE3MA==",
                            "siteId": 3
                        },
                        "3243": {
                            "id": 3243,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "amazon",
                            "organisationUnitId": 10949,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Amazon EU",
                            "credentials": "7KcoJhcMl9cT5rTLVAL58CRe2hSr87TkfK9zdFb2urppxk7EPnDoisnizeXmEKMFeYlaBwVoYJG/J8rIB+MwSKJkisYeQ3Az/egy0uwOs3UE7Z/UunXuA5xMRIZLI0GkzHYH0DW/67U6/zrSNdNuJ+QXgQLiKIVp93xExHup3yGD0YLHvxNSw58/RoWrRLpL+TXpT6X9VHfigokGHrIu4uwRnyByUyQHaAyeUGBxItx/pbeldHE1T3LJoY8JiGkoQwIUYCiE4jqoLHCwXqzZ9pWQG5LFNiSaGkA7WmOmTsWZbymL7Mg/YA7DzyjuFXS1mfdDFCRN512M1fshjF+X4psCnutqb+k8UgBvEHGedBJtQrfecrFRmkovlWD0Po62JCzpJAGCKIQ6IZqioKZQLEhD4J5AMcmx+CiVHku52/5WvByALEWvk20Mu4nthBHMnrc8whDd1HNzL+Idj6r6PaebVX22E1mb8w7pUUcu20lA9LNwcBoT4VDHHXtO5nPwD4vsPAUykf5es2vuEHZfhXDN4kdJ2s0ZS85dqxK53Qc8IsI19Bifb0Z4ofOFhFApFJ+8Bn6/Dcm8KahJzH76F2cj2bSevXflDLtlbSzQjILyYP9UifJncZLriu6mHKv+b4oshBCzIl03MdWXgoLUrHhgOiGB1jctzTp92TYX0iQwomQDHudS56ZaGGDBMlJtED3F6ojBhNBZQy+vIeBTcbIBgs60H7PgAjbPACtt/nN4zlw/eOFQfUEyWW3g9lqGPaIGwZJFUZeFC1F+DmfyReyoOqOaHhe9HRYWxFVx/QDjTZReHTHtSFIsAVX7HH8lyH8XnJqVKqdIztJWmikcoyj4Djj/LtcOFlktZFRHtZZFZW9i8/gKWj5LuA0QhlwgteoF0cY8KNMqYhpdghRggN3mU66tiXl0hSHLbLsPSORRdJgi4esMT7jXzvFVXhllCqycx0EkrIbnz2HTndq9b6lAdDTxxLIEPcdObHnOAPLPWxlmrxzxxC6922DqkqlK9bmkKtrTHDuN1DrGP2p+Lf3mdrTGoskYwVJ2x89mR0QnnQtQM3jjXst/ujwZfRKtEOMNx1Cwq3uqOSsCTt434A==",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "sales",
                                "shipping"
                            ],
                            "cgCreationDate": "2017-07-11 10:47:32",
                            "stockManagement": 0,
                            "externalData": {
                                "fbaOrderImport": 0,
                                "hash": "3e474f1d119d973829736ed9f7e18a699ff607bf",
                                "originalEmailAddress": "",
                                "fulfillmentLatency": 2,
                                "mcfEnabled": 1,
                                "messagingSetUp": 0,
                                "includeFbaStock": 0,
                                "stockFromFbaLocationId": 2796,
                                "regionCode": null,
                                "marketplaceIds": "A13V1IB3VIYZZH,A1F83G8C2ARO7P,A1PA6795UKMFR9,A1RKKUPIHCS9HS,A1ZFFQZ3HTUKT9,A38D8NSA03LJTC,A62U237T8HV6N,AFQLKURYRPEL8,APJ6JRA9NG5V4,AZMDEXL2RVFNN"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1563850808,
                                "processed": 49,
                                "total": 45,
                                "lastCompletedDate": "2019-07-23 03:36:47"
                            }
                        },
                        "3250": {
                            "id": 3250,
                            "externalId": "2015291000",
                            "application": "OrderHub",
                            "channel": "royal-mail-nd",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Royal Mail - NetDespatch",
                            "credentials": "TPAJ661Zv8weflgLAfGnxOUuidrIqYz7h2VF+DXL62F97ORyRp43wkY66Xf6AidA8MPWZNmo3QiBHLP5T7/mOZ7v69O1wf07NGm/1G9TvAH8RmJXxSmk069W9MTANKOgEkaKQYpSEyRG23qfYHx5bHgg9gM9+ljgEbbfpwVJSIMo0Ve18dFSGz28O5y74k7tcmbHyFe5NWjR2imIEkvQ75Ph4Dw6Xj2yY3d5W6sc4EjiRAJ7PH/01xkQFGuVkbFIARVjk8qeMnY9qOjuqrWoRUrJEpldvcuTj6VSwjtsImEDc7S8vcBuqtuLHQACUqi0em4OSOsEUa6Uty7rodNGhArJLkhmHX5KMX+tjc+tqunrHgTk1PW4OP0gqJx3PqFKnS/DWlQ1DzNe4/OhBzwQF2+zi1ovdVJCtj5Bt+L1fpYGad82rwHa2j6mTTnQXPNGaa3uBpXKDALBz2/s/XZNIXElriH1/h+UCMupDfDiCiDJ2SeHU8J9HnraiYswLVmlUqL468lqoL/9ALeqXeLzIW0LM5lFz6df8cpZtTCV0Ew=",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2017-07-11 14:38:55",
                            "stockManagement": 0,
                            "externalData": {
                                "accountType": "both",
                                "formSubmissionDate": "2017-07-11 14:38:57",
                                "domesticServices": "",
                                "internationalServices": ""
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "3252": {
                            "id": 3252,
                            "externalId": "3252",
                            "application": "OrderHub",
                            "channel": "myhermes-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "MyHermes",
                            "credentials": "7f0kv+R/TAgt0FTTQa/jAgjjqKeSJJEqscqyXH7V3jN1DE4phocq1MLfFFYGJ0a7AqSagcRmtARNBXvSRlw3nzboLdHgfTKdaOybiEmDmID2zI1cmpNdhi3h1wPelhCWOAkoPGPSCXyndPc0AzVDWHzRte2v76B5WJM7+QuVKgxxELxEMjub5BlN/WbQhjho/rCSTfQPW5Dahflawhb8eRPGKgFq0IdymRAikIXylt0ofznpXkIgxdiqvg9duxViHxmPQ41643IDrsosKt41Bm66fYg4e2WlU00l9ryf7upXbOlhKTFpvHDEDBy/GpDIMp2uHWf10fz1QzOjxdK5YIGwJF2mO9br6rFoN/Y4AVc=",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2017-07-11 14:43:26",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "MyHermes",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "3336": {
                            "id": 3336,
                            "externalId": "3336",
                            "application": "OrderHub",
                            "channel": "dpd-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "DPD",
                            "credentials": "gQR0bS/8kMl1ZdPqxuByLRgIzhL8ifWeqzS9R90xxZP/0lQR5ZhTPJHxLRD7FfS8PLepaPkA34Oix82txMxXWBzj2oWxO58d6+NV8s7QjYwo2Nt4TcljqCBUa2q9ci2bXAAEaFr1Rmnn/Q/DfLyS0NWbPbDjknBP0+//MK0BHZaHU4sQlMTe621Bor8up9S4jQZpUJpc7uksyCJwxG8LlhzNLlOIB7bov+KWx4zfUtKs93uGnGlfoUXylNVFCCwqJBJkyB++HMZIY9HPslnQ4doB8U8zwTV0zcu3hUdCWahbeEPSR8/zIOQn9GyOftzEWqa/3qB6VwLkg1DbtVU8DyCIbhcrzLaz9sOkl9XnMid+ZT5Gp3+w0auL7svxqiKSjmDvG81uifFbJxZL23Xk8EBpl9Sfy2/kTwpVlOlB4sy0Mm2zN1HeiJXb54tPBt7plNPYFWtxF83Ij53uL+cDpPzot8KqlK4DPQ92Yr6xRqoAqY1Vsw5okylz2k48Rw8q7sipDQYuza+A5v8LMjGVQzpl/gl8rh/WtcNxvyL+D4vSWafxx7GfIglUfschK9EZgxp1pC26UWBt7B41zQQIaSqKRmiCKkN8ZxrbQ5TyHUEY8LlZwJBwGxEye2qW6mL2",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2017-07-21 14:20:27",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "DPD",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "3337": {
                            "id": 3337,
                            "externalId": "3337",
                            "application": "OrderHub",
                            "channel": "interlink-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "DPD Local",
                            "credentials": "VsgnLNrzpX0lbxDf762CWR/zHY4J57fFZIPPMuHBWSs7q1woeJfGIhJNV3Q8aJufgNlA6g9ipRofQjMMT1GcGEcus5TQYzLrMag3PekIzx/LeW9HxYeJlS6+mVJ6pNki09+ePPLJfUhkZ0DgTghmh0Vk2/0Qst1ur9IvRmdjNoqDtCF9avYJlq3q2nxxf8erS1x6QwMe6h5BM4vOwJc44bt92/ioK6q+KaUJYrbEKDTv+X9skJuACyDVkrdC2o3KquD3ie2hKWfQ1BeXhlfId1WmO0KXFKZpn0bVAIsYPadjlubUmIxBw0IZ2vLMkkVQeJnmIIdmQ1ZDpD7YvZWNN/wYzHavIBa6+0UEeCfr1AlSw0bhg/F/ZWn4j77njvalqMbDQjFaiTALO2NofS3htvF4RQZVwEYXEKwtYZyaiRKotIY1a2tYAWOQmVJOluTPZfwD3WTlvB26xfE6ENiK7sgLiE6/dbrN4xTjAsTAB6d2Z0Hxm1Oa0xaUIy0/Pyg9oUUCkwzAlyP/dpXbtMUQiLbJxJg9uVMLw44lBf8WXVz5KcJK1/Hw8ZDfPWMz+dIj0uInuaA0kVyxz/xx8IUKSf21a03DibXKRGKDM0WSlhAO0+1gSqPQPLkw+srgAg0sRmzIXBk5rpq8opXd1QLv4ugYvkGs7rh+OU7mcl0rNIHIT+m8Hpn3SDeW66PYhnxqtmX2PAJ7K+jDQaKxmCguEA==",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2017-07-21 14:42:40",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "DPD Local",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "3747": {
                            "id": 3747,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "royal-mail",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Royal Mail (PPI)",
                            "credentials": "Royal Mail",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2017-08-24 15:38:05",
                            "stockManagement": 0,
                            "externalData": {
                                "PPINumber": ""
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "11660": {
                            "id": 11660,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "shopify",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Shopify",
                            "credentials": "2nfg+u+z7qgiHaUqL7yEp5wBIVm2mDW9bW8IRa/tbJ/NVwBsRfGfZD3QNc4CHhkjidsA6bUMGlGTIcVhdvsB+yEecd65eRhg82xhJ6Phmwg51zsVENmCtRvuQ2tjJGpibW3M8gGAW4IJ+5eAdJbvG9jT9+OqlLLGVK4FSZ9+iQoHjKsQ6DqoQd892BOl7dFkcKLmSbKEAXQQXkRU0D9sMbecSmACoa0CSfBCGTEqgOE=",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": "2019-06-26 12:25:37",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2018-01-04 16:40:43",
                            "stockManagement": 0,
                            "externalData": {
                                "shopHost": "dev-shopify-orderhub-io.myshopify.com"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1529671637,
                                "processed": 1,
                                "total": 1,
                                "lastCompletedDate": "2018-06-23 12:47:32"
                            }
                        },
                        "12354": {
                            "id": 12354,
                            "externalId": "47fwg8cpdt",
                            "application": "OrderHub",
                            "channel": "big-commerce",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "BigCommerce",
                            "credentials": "nYg4OGzKeMmDeVdlbKJRBg3WLTlxIWOK0or+WcNloytYBe91o1iu4DDfhTuCiat72+b+3G8yCA32SCXdB2+mzBxnW2mkSfUq73gHOKhQpNkBiA5dT+XYfHokcdHJErxWps+QtofTX0zFmINbvZOvmAvPpLdUblNMW6DI7IJ2wHoGFlo0UjB4b1PIZJ3HbgDZRD3RQWCIHbXgxpuOAKAJx6dHVtnx6hxqVGMrXB2CApU=",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": "2018-07-30 15:12:37",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2018-02-19 11:20:51",
                            "stockManagement": 0,
                            "externalData": {
                                "secureUrl": "https://store-47fwg8cpdt.mybigcommerce.com",
                                "weightUnits": "kg",
                                "dimensionUnits": "Centimeters"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1521127671,
                                "processed": 13,
                                "total": 13,
                                "lastCompletedDate": "2018-03-15 15:28:15"
                            }
                        },
                        "12355": {
                            "id": 12355,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "ekm",
                            "organisationUnitId": 10949,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Acme",
                            "credentials": "Xu6vYvSef2w9DAA3fiZvhLYknLkLPyN9lGehw++yU23TAPfkgFESdOvWztrilx4/twu7etv1q/3xrxpQ2ZqZ6lE9MqsxovvQCRYZRTD2anY08cuzlDNJ5Xm7cds/SPcuA0bLkbkeO2SFgVqrc7cF4fJYfO/FLQOY878LYaTvFJL9xT8jx93gzf8TGDctB1IABpkLG3kaZ/7t1gD5adBukAbhzu9CA46r0YyqU4rDqFTGDS2BVp1z/p31ZFTElA42nRsHJdoJ+Q/ICfjLfD+NlELsRWne1dp4Y0x7FzZ6djfcS/ZvtWoPexv7Xz0VcGdz4Bz9odLqI50TOFJ+GPVOeE/XX9k8Hk9Yx2P/j1R082HZpK/NZlKdCD8ovh/g0oO4dSaZNYpKoZs3uUWogN56GvpQlxUf1CtorODaTTwBPPbSpSAEzzbtMfzJJSdbot6lM/hOyurtuwEAmao+V4jZwX7Pqq5DqrTXVZcHTtSUZQ2BXOP6W0ug07rcnXAXAd9dNcmld5d6ywHFhO1U3osZYrbat5niVRORBdmAu3842KhfuHefTnZ9D34H2YpqcR3wVsEd44oPgSY3EJU9n/lvUj/Aohn0Oz+uoOXlXPCiNBl1wQQ61CZSRsQqCF4tWgDdQsUb0wEJJaUSJ/JcnFBiZyEeTk5TnghbxhXSpSvuDcBNgh2cRUhal0mf/8+zRY8rEymTDzRxXoUd9InS3YJS64dfwJvJ4OOmq0bI1BXlMXs3IX/XAzS/Cyj8oAcw3eIx",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": "2019-05-31 22:01:11",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2018-02-19 11:37:54",
                            "stockManagement": 0,
                            "externalData": {
                                "ekmUsername": "channelgrabber"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1560910676,
                                "processed": 392,
                                "total": 392,
                                "lastCompletedDate": "2019-06-19 04:34:37"
                            }
                        },
                        "12628": {
                            "id": 12628,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "woo-commerce",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "WooCommerce",
                            "credentials": "n74zEWIEcNi2aN1IiseeY4EBTnZxbT+pIt/V6XmdNHJSxK/0SPaiiSWKFbV0qSJtk88YwMaTf2wIgN+32hsCPIertpodyhhccXY1D5C72z07zEe5R48fxaRtPOcZDdbcwQzKbdz6qMta9o5ZyAlsVqYmezGrQ+tugX0sx4wACg1caskGqzGSjsrXMsSzTeG5/S7B1kT9qhXXE7vGBks03Q3l1RK2lbdd1ilO1WorAQZVtZuCugBuPuINcADQ7RhkqloG7UALR5QuF3oTdofh5ZrqKwx8c0FCQErZKn5El9iWO7NvgaHybiizYrIPDUoAacRxpJXx8Z4BjlSrItmwlIMC1XPr/jzOh9CVU/9i0Vo9BkoOpHGXP0ykzP2fHdw1hRaV3UbnEe7QnR5Oqf1t5wdfCNOuVEG3cNTJqSY87l+6XBtN0918lq1vT8p2A5n56GuKMRsuzrb5afgNAfDmXA==",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2018-03-09 14:20:55",
                            "stockManagement": 0,
                            "externalData": {
                                "decimalSeparator": ".",
                                "thousandSeparator": ",",
                                "dimensionUnit": "cm",
                                "weightUnit": "kg",
                                "taxIncluded": 0,
                                "currency": "GBP",
                                "sslEnabled": 0,
                                "sslUsed": 0
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1555459231,
                                "processed": 92,
                                "total": 92,
                                "lastCompletedDate": "2019-04-17 00:02:27"
                            }
                        },
                        "12917": {
                            "id": 12917,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "royal-mail-click-drop",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Royal Mail Click & Drop",
                            "credentials": "FyGQTsTo6FxxEgfZP5u/mg3S1GYllTb1Fy7Rs6Va50hJKMOPelFoKHpygFXmXHysCbIR9GpAjIdCpopxwHuwvFAe5o1azYz+WlSKG8VGPJuDDFPhZ2dFPlW2s8DScHpjFO2TnH2D+7DmauR1W/Ttm2v8FIoWY2Go7+S+GE3fq1wZmf3eESt84Dn8hsx39lzz",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2018-04-03 13:25:10",
                            "stockManagement": 0,
                            "externalData": [],
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "14098": {
                            "id": 14098,
                            "externalId": "14098",
                            "application": "OrderHub",
                            "channel": "dpd-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "DPD",
                            "credentials": "uUXAEpiPi895LUMm4TvfhhQDFNZUt6mMfjkRyqEKtBODeuPnx2f5EmQUhp8AQmYbMbyM6B7amdpHW0alXAG7gt/ufw1Ndhb+iK/q8if8V6pNK49HGba7f1jY5/0d9+QEN0TAeadR0eSZeawW1BweyvpGx1b6sLF21ejgecRKHPtFPniv/Ym6EO26D9OSyyZSIZygyintBBX7r9fnCiCA2BRk/IR49CrdYTz5jeEubd8ARsY7MJXZjE6O6/TePqYKzXio4Q+GnA2i4Tc+dt9WbfaiRHQjpT5daot6wDEWeIDtm2fe4w+f44uuitY9S5zSVdcMFO1Piu7LPk6ohoebtoK4OQUZpQVJDMNlO8vgX4zbgT1GxYzDx1FdVtniKnp6eZbt3rp+2h3WNbN27w3NoMWQa9Lo9SHaz6zHgnhTveTFQ/oW142T1n3wEzE4qKAurT2hOix61b2uUO0wP9R95b2v0ryLxLRjmI2KX2pp08UBa9VAgAh22fI1KhK38LVigCE+0doPUcEnF0FeogihMdtC9ZFRP13az2+sfw/p+STwbCpujEdfg4qpDLRAkE0saEUVBBpzSA4ipunEm++PrsqDv2jXlMGeP+ViUD+m/BcW5von+M/d3q9JsjT7hzak",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2018-08-23 13:00:52",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "DPD",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "15504": {
                            "id": 15504,
                            "externalId": "15504",
                            "application": "OrderHub",
                            "channel": "royal-mail-intersoft-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Royal Mail OBA",
                            "credentials": "cbwMXR2z3oeu91Jcqi8PbLZwsbe6mRSmKotJNhSP740WFyH/xN+I8AzruE/RgDS0urTeMdRLX/EaaeVaVszk/SnJVyih4VJyOmskqrzzGVrA5iaPGM1nLcY4rwzZZd9DFNLYqEoSbqgJ141orsmCLVL2ApOLY7SpfePZywCmFnKlTJ3FDsSLix4kX0wjuIWi3QJYAMFahOrb6RXR24BVijZ/x9mniiKUBkzJZccDuvGcpRLcNWgnl//d/Hspnum7fmCjhcVZpeKcTjyCPlNKyrbwqtYYCyiADvh4zzQt1rFPR4DlSE6z87M85yPTiAr/1UWTohXOrE/vOPSZmxw5cOg6Y6weDvi2GDtu4CZvR2+Q3k89NyyAIT/VLGMugWmJKVf+aibk6dpd5E4URa23CA==",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2019-04-18 09:29:14",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "Royal Mail OBA (In)",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        }
                    },
                    "stockModeDefault": "all",
                    "stockLevelDefault": null,
                    "lowStockThresholdDefault": {
                        "toggle": true,
                        "value": 5
                    },
                    "stockModeDesc": null,
                    "stockModeOptions": [
                        {
                            "value": "null",
                            "title": "Default (List all)",
                            "selected": true
                        },
                        {
                            "value": "all",
                            "title": "List all"
                        },
                        {
                            "value": "max",
                            "title": "List up to a maximum of"
                        },
                        {
                            "value": "fixed",
                            "title": "Fix the level at"
                        }
                    ],
                    "taxRates": {
                        "GB": {
                            "GB1": {
                                "name": "Standard",
                                "rate": 20,
                                "selected": true
                            },
                            "GB2": {
                                "name": "Reduced",
                                "rate": 5
                            },
                            "GB3": {
                                "name": "Zero",
                                "rate": 0
                            }
                        }
                    },
                    "variationCount": 0,
                    "variationIds": [],
                    "stock": {
                        "id": 6945865,
                        "organisationUnitId": 10558,
                        "sku": "EXBLU",
                        "stockMode": null,
                        "stockLevel": null,
                        "includePurchaseOrders": false,
                        "includePurchaseOrdersUseDefault": true,
                        "lowStockThresholdOn": "default",
                        "lowStockThresholdValue": null,
                        "lowStockThresholdTriggered": true,
                        "locations": [
                            {
                                "id": "6945865-464",
                                "locationId": 464,
                                "stockId": 6945865,
                                "onHand": 2,
                                "allocated": 0,
                                "onPurchaseOrder": 0,
                                "eTag": null
                            }
                        ]
                    },
                    "details": {
                        "id": 1888937,
                        "sku": "EXBLU",
                        "weight": 0,
                        "width": 0,
                        "height": 0,
                        "length": 0,
                        "price": null,
                        "description": null,
                        "condition": "New",
                        "brand": null,
                        "mpn": null,
                        "ean": null,
                        "upc": null,
                        "isbn": null,
                        "barcodeNotApplicable": false,
                        "cost": "0.00"
                    },
                    "linkStatus": "finishedFetching"
                },
                {
                    "id": 11409247,
                    "organisationUnitId": 10558,
                    "sku": "EXWHI",
                    "name": "",
                    "deleted": false,
                    "parentProductId": 11400129,
                    "attributeNames": [],
                    "attributeValues": {
                        "Colour": "White"
                    },
                    "imageIds": [
                        {
                            "id": 13812565,
                            "order": 0
                        }
                    ],
                    "listingImageIds": [
                        {
                            "id": 13812565,
                            "listingId": 10222599,
                            "order": 0
                        }
                    ],
                    "taxRateIds": [],
                    "cgCreationDate": "2019-05-04 15:03:15",
                    "pickingLocations": [],
                    "eTag": "1f3c6c66129520b0baa32491555e183f73b5cbff",
                    "images": [
                        {
                            "id": 13812565,
                            "organisationUnitId": 10558,
                            "url": "https://channelgrabber.23.ekm.shop/ekmps/shops/channelgrabber/images/excalibur-stone-not-supplied-103-p.jpeg"
                        }
                    ],
                    "listings": {
                        "10222599": {
                            "id": 10222599,
                            "organisationUnitId": 10558,
                            "productIds": [
                                11400129,
                                11400132,
                                11400134,
                                11409247
                            ],
                            "externalId": "103",
                            "channel": "ekm",
                            "status": "active",
                            "name": "Excalibur (stone not supplied)",
                            "description": "Wielded by King Arthur!*<br /><br /><br /><br />* we think",
                            "price": "2.0000",
                            "cost": null,
                            "condition": "New",
                            "accountId": 3086,
                            "marketplace": "",
                            "productSkus": {
                                "11400129": "",
                                "11400132": "EXRED",
                                "11400134": "EXBLU",
                                "11409247": "EXWHI"
                            },
                            "replacedById": null,
                            "skuExternalIdMap": [],
                            "lastModified": null,
                            "url": "https://23.ekm.net/ekmps/shops/channelgrabber/index.asp?function=DISPLAYPRODUCT&productid=103",
                            "message": ""
                        }
                    },
                    "listingsPerAccount": {
                        "3086": [
                            10222599
                        ]
                    },
                    "activeSalesAccounts": {
                        "3243": {
                            "id": 3243,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "amazon",
                            "organisationUnitId": 10949,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Amazon EU",
                            "credentials": "7KcoJhcMl9cT5rTLVAL58CRe2hSr87TkfK9zdFb2urppxk7EPnDoisnizeXmEKMFeYlaBwVoYJG/J8rIB+MwSKJkisYeQ3Az/egy0uwOs3UE7Z/UunXuA5xMRIZLI0GkzHYH0DW/67U6/zrSNdNuJ+QXgQLiKIVp93xExHup3yGD0YLHvxNSw58/RoWrRLpL+TXpT6X9VHfigokGHrIu4uwRnyByUyQHaAyeUGBxItx/pbeldHE1T3LJoY8JiGkoQwIUYCiE4jqoLHCwXqzZ9pWQG5LFNiSaGkA7WmOmTsWZbymL7Mg/YA7DzyjuFXS1mfdDFCRN512M1fshjF+X4psCnutqb+k8UgBvEHGedBJtQrfecrFRmkovlWD0Po62JCzpJAGCKIQ6IZqioKZQLEhD4J5AMcmx+CiVHku52/5WvByALEWvk20Mu4nthBHMnrc8whDd1HNzL+Idj6r6PaebVX22E1mb8w7pUUcu20lA9LNwcBoT4VDHHXtO5nPwD4vsPAUykf5es2vuEHZfhXDN4kdJ2s0ZS85dqxK53Qc8IsI19Bifb0Z4ofOFhFApFJ+8Bn6/Dcm8KahJzH76F2cj2bSevXflDLtlbSzQjILyYP9UifJncZLriu6mHKv+b4oshBCzIl03MdWXgoLUrHhgOiGB1jctzTp92TYX0iQwomQDHudS56ZaGGDBMlJtED3F6ojBhNBZQy+vIeBTcbIBgs60H7PgAjbPACtt/nN4zlw/eOFQfUEyWW3g9lqGPaIGwZJFUZeFC1F+DmfyReyoOqOaHhe9HRYWxFVx/QDjTZReHTHtSFIsAVX7HH8lyH8XnJqVKqdIztJWmikcoyj4Djj/LtcOFlktZFRHtZZFZW9i8/gKWj5LuA0QhlwgteoF0cY8KNMqYhpdghRggN3mU66tiXl0hSHLbLsPSORRdJgi4esMT7jXzvFVXhllCqycx0EkrIbnz2HTndq9b6lAdDTxxLIEPcdObHnOAPLPWxlmrxzxxC6922DqkqlK9bmkKtrTHDuN1DrGP2p+Lf3mdrTGoskYwVJ2x89mR0QnnQtQM3jjXst/ujwZfRKtEOMNx1Cwq3uqOSsCTt434A==",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "sales",
                                "shipping"
                            ],
                            "cgCreationDate": "2017-07-11 10:47:32",
                            "stockManagement": 0,
                            "externalData": {
                                "fbaOrderImport": 0,
                                "hash": "3e474f1d119d973829736ed9f7e18a699ff607bf",
                                "originalEmailAddress": "",
                                "fulfillmentLatency": 2,
                                "mcfEnabled": 1,
                                "messagingSetUp": 0,
                                "includeFbaStock": 0,
                                "stockFromFbaLocationId": 2796,
                                "regionCode": null,
                                "marketplaceIds": "A13V1IB3VIYZZH,A1F83G8C2ARO7P,A1PA6795UKMFR9,A1RKKUPIHCS9HS,A1ZFFQZ3HTUKT9,A38D8NSA03LJTC,A62U237T8HV6N,AFQLKURYRPEL8,APJ6JRA9NG5V4,AZMDEXL2RVFNN"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1563850808,
                                "processed": 49,
                                "total": 45,
                                "lastCompletedDate": "2019-07-23 03:36:47"
                            }
                        },
                        "12354": {
                            "id": 12354,
                            "externalId": "47fwg8cpdt",
                            "application": "OrderHub",
                            "channel": "big-commerce",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "BigCommerce",
                            "credentials": "nYg4OGzKeMmDeVdlbKJRBg3WLTlxIWOK0or+WcNloytYBe91o1iu4DDfhTuCiat72+b+3G8yCA32SCXdB2+mzBxnW2mkSfUq73gHOKhQpNkBiA5dT+XYfHokcdHJErxWps+QtofTX0zFmINbvZOvmAvPpLdUblNMW6DI7IJ2wHoGFlo0UjB4b1PIZJ3HbgDZRD3RQWCIHbXgxpuOAKAJx6dHVtnx6hxqVGMrXB2CApU=",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": "2018-07-30 15:12:37",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2018-02-19 11:20:51",
                            "stockManagement": 0,
                            "externalData": {
                                "secureUrl": "https://store-47fwg8cpdt.mybigcommerce.com",
                                "weightUnits": "kg",
                                "dimensionUnits": "Centimeters"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1521127671,
                                "processed": 13,
                                "total": 13,
                                "lastCompletedDate": "2018-03-15 15:28:15"
                            }
                        }
                    },
                    "accounts": {
                        "844": {
                            "id": 844,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "ebay",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Calico Trading",
                            "credentials": "iCpnvOwePsMJq7J40bqlO77erZ5X+00dzKvuRk4PpSGCEsEYCixvrALXTh8lZ4anCsePIJMLRFc8MN0C2DNL7WWBffE20qfU4ZmfY6BtJjLVpXn3Y8/aLas6uI7BYX+xydtAavkSmiGJOLPEtQZqCpHT008zPFTA59ebB4tDe2DvZHIZAPoxMX+QfpaaujyBxpzw3RGmId4C6LzUJ2G5meV8tzw92/SMU5alnWCrX+p1LUK3tk7CJRFDU6PSOn8Lh8ZegQEAoMUGMEOCZuIvhopDmYiCm2PLvk1f+IofZXTufQtAjZBS5yyDTVqqKSS056zp02tyh3J0aATDFpVONkJ3IaTFRNpH0eG3nwwsI0RgaRPTNVr/c2Nhf/KblTE0P8iOus8UJZTIesgXQApt2yvUr/P/X/VD0gkXZO/nREmdRqAerC1Usx5mCLvAUBYoNo3el8jsdYFX2ykzbwFd0cHJGaQPujEdjmR4ELs/llTelUGT6v+MIrfw9cZQ8SrP2OziAP5lsrr9tqi9xG45dGas+/jCOWdU8eAxm5rcQEtDlWG1Kk74tbwWFLqMgrKIVE+yx5Xtud+cKgEp2IDD+4bc/7plEJBW0XQ6nMJPelfKq4DnQe4vw0hcgyJjAzJFyDQtN0xVlOmciVHRi44PTgEFKTVUmwBzwvxsNeUR1an5qeZ67gOxRHgndI0QVq3aKI8vm8+1arW1Hg7iYYbdoZ0L+Inl+SGRdQFVwfvgmLjV9YacJV4o/m2X/RUawj7i386r1HSitafwnICDgsOk/psvSb7phj4Z/2jxx+E5VjlW38v6bVpk6UYuGimbVyh9gqNGq3oX1rRPG7jAiUQTGIoSFt56BJFAEyDMXqNnzv3c/nYm+gTw40pmhPPAUMb30ZMecHdIG5ulqgaQaxADOM3Lc4VddBhFO9ejdIrACf+Az+TI4dzGgDnh/62yrS6hkdP5qR3N1LUQmyEgDH386oy7uQsoe57Dnuy29YNI9ijjC/3Zlf0k/O0SzqFCKGDOTOWPDA8yj5bw4ZnwyWE0Sl7FF3QshrhdmMlJ8hZz6oE8M3J8ynNPNzHl4k+ItplWSp+tnOgMv8r6CZ3/jvW1vfUQ1z2kzw7g8dt6NcQjFTbCAriDdhJPYTgeOtDRwaWpHuFrezA0suhYNVo/7CUyGzkOk1XFpMazNlBUKDFHFwGAHLMRLTKivg0r/8pQzoqROxUjDedGs8YXQNRAkQzdQx2cTEwW4yJNrEn9j8nFD+84l5j+xKTQfwkbfQ0AzVBO/psTYA4PAZDArtxqxiTroiMNdaZ3P8vXDpojkardR2QKsQEEoInXaGHpNzxLVdnrZcbRBCZMaWacecUH6H7vE41PAnslbm6E/0h1gCHK2tqYCLH1M/iYTL/hp64nPlPyCb3P0/TGu/gFcamxSRqPF4cP/MnENAtgIW9UxRsEEUbMVSvYxg9MtkADggF9pmL2L4Crkj+FbTZ7+yhRxhU2ycwbhZzoEXDOqPauxnDEXIbXlV0gJrUnhwIcA0NQi5JkyZukM3HjvWX4j/MB1mFsKlA0wdfVYmh8kIFr6bLCfjuipbC/sUIB/93U+rvSGiaVNqM52w6dJjIQZ+p9eDJzKyHy5JIipPRhCcMpBx5xnUA9rlwhOhy9wKzxRfUQApXOPu2MavivSO/8cP5mLdkylbH3T1vBBcuSVcHhQ+Wvhpd4R1zIAt8EtZyfSJgsiw3EsQHXfebAoKffXQNKX63T2bXJi4WAOrRYjPAsey+YmHk=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": "2017-07-20 12:16:35",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2016-01-27 12:16:36",
                            "stockManagement": 0,
                            "externalData": {
                                "importEbayEmails": 0,
                                "globalShippingProgram": 0,
                                "listingLocation": null,
                                "listingCurrency": null,
                                "paypalEmail": null,
                                "listingDuration": null,
                                "listingDispatchTime": null,
                                "listingPaymentMethods": [],
                                "oAuthExpiryDate": null
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": true,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            },
                            "listingsAuthActive": false,
                            "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=ODQ0",
                            "siteId": 3
                        },
                        "1096": {
                            "id": 1096,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "royal-mail",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Royal Mail PPI",
                            "credentials": "Royal Mail",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-05-25 09:55:08",
                            "stockManagement": 0,
                            "externalData": {
                                "PPINumber": "HQ12345"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1445": {
                            "id": 1445,
                            "externalId": "1445",
                            "application": "OrderHub",
                            "channel": "parcelforce-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Parcelforce",
                            "credentials": "2+p/GVQs1ndg/heKwGT6bePmrr9ElapPzIhmSYdggFDPypxFY+/sIyYl5nWNhBpugPdB/rWFnyon41Trir9I1tPLadwkG3tx4nXqeN1Fs417/NKHRZtZw2pYcLAOYiJO5egBD/wtYAVOWwTie99HiBsOXxjuOifLQ3/eoo2lgorjmnQeRJ5sKY535YOsHS3m0F47C2ypo5emUIw3pXCoSncxdDydOmrY0H5tJLUIA9nGZ7DDuNBQyfFuu97XsIExuriMw3qIg9MXPcAFy56silpxXdE8qMAlIN9NNJQqlcSOt++u6XpoeO6FEHXmvc/186H3Pi/XXwp/xpr7+0Y8FK6K0/rPga17hGWRLY+AidVnNyYl7qc1LljcEmhSXD58fpzMIOcH6XRjiV/giHHZ4EqTKBMIBpxwJ8fpqpJAGAlGs7t05vol/44LQ37cVzNp",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-08-30 14:27:11",
                            "stockManagement": 0,
                            "externalData": {
                                "config": "{\"emailNotification\":\"1\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"1\",\"sms_pre_delivery\":\"0\"}"
                            },
                            "displayChannel": "Parcelforce",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1447": {
                            "id": 1447,
                            "externalId": "1447",
                            "application": "OrderHub",
                            "channel": "dpd-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "DPD",
                            "credentials": "ljkIEIyzleoeSE6GrLzXJXh9nlRkVmiWu+tEm023Qsld2iu0461qm3LK9ZmwxZ110Jh/PBp8E1hUuUd61B/7cei8QWZcF8qjAq6IyZnkL+MygqJrScSdbowuiFSJfsw2oKiNH5pkLZ37HMyi/s4bNkCTOCzNIF+QBeWDX7GEXwXAkBhMGUIrQcXrjvf/aJV6+9D2Wv3TZqXRrZHg8HYqL7KJm1f9FGQ5H6Fxsn5Ams7+qTcTfV4nxKB7mM2aQxLbPF2rz0B5UU4kKQgLjc6p6ISTm+HRkEPqo+TQMZU9diBQOlrEm5MPBDK/y/QKZf8SqtEG7L3VKSw5pbpyThRUvcEeWsq5eW+r3zQ1bhzOewYHHD3psQWUlWlWC2+ERO430xrYDiihs5gOBhtG5rYI15g5Hz7GrRSPXTJl2KHeOrwTUnKVdmgOTYFBNwiXB9yHAMw79394xLhEpgeoZAon59z+n/kgCV+xf3164Up2DNB4ZXeC0bKCwZS5UU1aqGV8imcBrsh45MlaF/jDeRI+ZoWhOUjGdJZrqibPhAKnOG0PW4028tQ7WUwl1Q8qZ10AQRqQMTIChoiTVr/CYJ+P+fW0redHDDXzi2jSa4sp9sPnsmkCIP0wuOkZU3yxawpi",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-08-31 10:08:30",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "DPD",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1448": {
                            "id": 1448,
                            "externalId": "1448",
                            "application": "OrderHub",
                            "channel": "interlink-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Interlink",
                            "credentials": "NhqQH5Yvo2mTPMAitLN8XOZRGJjNKbu4ld0zvfLH195fntGbuiTG++69OzEB8uB0xUkNuWl26t4ou+Xl3A3pG8Nj4doKnuE7Tnztrn82wVrGMkJHElVGs06ffZdFvG0s2MKehldhazxC4ycuEbjDX/AQZkOTULoat+XeDeujvZnN2xxB+o7xpx5FOjeJTyyypjoFa8MEtGQovHpCPYO7ph/Av7MU15q9doddvrARtiIEK987xXMSnei8Q+eauGWUs+74javCusSj0u5QKPLOoph/iUNtsU6XPuMgSbUvaNpQBIR4HVo/ztnXlOx8JeNC+TrnBQX13N+5I267uYhMNpZyh3I4jj2IE44WeJvWCCBCw+68U3UR4DMccBKx5ClJkReekIwl9D9KNO+dK1lEjL62B9peb1JQ+RgFeulo1XB4otF/cJXD9MeliZVDF8P2rR3v7QhyHfpMPQdOu8w2/blnjpu1PUdUPQhUVACqPNJjYpLLSeSWZjZaTENJs/lTTmOSUAMYMsVwCNAWQL8zpgxNvK3PmtStI9g4uNhRPUUgt1d+L+Pu/wSqkVhqQ24YbahGfPHKCC09QODqBBkgaHk0IlcVIsKLOJ5efJBCP79HOpeN5ZvZpBhhni+yAhDxeRlk996cQJGl85xiVHGgU6Tf1KycBa+SWeKj+y90s1aKVU5yLhEJL+DNeq4vXHWMt5KvQoA2si8GMUoKzDnP1w==",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-08-31 14:28:29",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "Interlink",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1456": {
                            "id": 1456,
                            "externalId": "1456",
                            "application": "OrderHub",
                            "channel": "parcelforce-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Parcelforce",
                            "credentials": "X5d7L4B6DUXntVIsKEC6J7ULjviSYN9GsxICofFbFW6PswrPlEmAdeK7IU7ZjFrFRTPaP7W6e/Iz+jG+KqKQNCLVF+B2ggau5v22zwx4KGTl1+9TYhkfhGHXhA95m2l5sVBSNOiSr9ly/kprrBXa7l22ouWiXYXt2Fzcx2VbDjYd4zAIN1Tp5N80alyfkRzVM/RoQJ9IwFVoFHqMXE2FVPUz5VAriZ9LM5DTJHUYuS2bZ8d+s8c4BOzrwi7NZhEzzsbWtDF9gKNRKc/wqKW3idSMPGvSJTnjCGMH9+7FxHXhYN9BE/igqnluhIxUHttJ7A4FQw3yEypyDDybfJzta54pGULumsMmqkBSOZ69YgKCrYpgxfZdhfnzmy8hIiAwoTOZVsgQbBP4rcbFyyD/O+pXGuVh3IDeclenPbv3i0jMu0SsVFDwI5QcDoostNQMbhCe/+nuTvREI1p86aJyAA==",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-01 11:32:39",
                            "stockManagement": 0,
                            "externalData": {
                                "config": "{\"emailNotification\":\"1\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"1\",\"sms_pre_delivery\":\"0\"}"
                            },
                            "displayChannel": "Parcelforce",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1457": {
                            "id": 1457,
                            "externalId": "1457",
                            "application": "OrderHub",
                            "channel": "dpd-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "DPD",
                            "credentials": "fG7mb7o2273LKUcn60vpLqfgxMp0G7LArSWNJBhjacburSEe4dIIXIwv33UPngs02UrfSOtf6YxYLQ6+efXNa6NbF4r1WlcVlbsQq4kBrkPtHWnfJE/IUgj6FNC0p4vqMB/3bwaV6f/gJgkSeTMmTGnRtr36icakeFbgOG+n4mBJhMpH+CMErlhJnO3+7Kq7PoAaA/1EZyHSf5hMBnrU4ZBrFEaGChToDRaiZGPgAiFWs02BlzVXAFLQou3FD+UauH+zbW1kRXCd+OOYTG/ew4yPNPB8SC3CCHwci5QiESVIs+q/qCApLMBVPVq6/EA8bghNsO7VllIRhUqNaHC/X+K9IePaplS38FV7nNd8twLayj0Fv7JSNqD8BwgVWM+p5geadxX9T05fQ5ijqfCP3qablNY1hJWDQMnxbvhExxjSO0BPvaafYOHE/HiokdsCDjLiiBCa4q48O/tiLMgaR0kjpFmD8xcmZj5+fPKTCXKd6jssI9pTEtoon9dQhCo0S/kF174ke7r6vj/9lKr2rTdVGlhNoqhhxNet3AeXppMk7PZ2JxpiYFQIy3CTuCs6Cce4c3Gdn1Ws/iSZi/9PpMhP/hvUxYDO6SMN5AmI7S0=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-01 11:51:43",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "DPD",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1458": {
                            "id": 1458,
                            "externalId": "1458",
                            "application": "OrderHub",
                            "channel": "interlink-ca",
                            "organisationUnitId": 10949,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Interlink",
                            "credentials": "xt5MOjdt8njG8ACdxh7Bj0wTCGxMqU0wJXIoG8bM/JuUSSpCM7P+/P7OhAjROM1jnbeSBpT9UDmfgc23CaR2kW/ebcVqrRMwWsxDoC0yzR/adLgTn+TnV7JqGWYV2Te06IV9otvmWG30mOSvrawVTXM956dun/Al/hUAM2E8CJFFnG9nG11DKXfa7CB9X9PeCGGHq+YRuK/n7xI+s7WblT+BU1YSIyhGiSvzKCYIrNtNwDjq7m8RqDsCtYNGAUAufF2pACZKU5L/YF7ClH+5pzwAFalqepI6GjrnMkO5gIMHA1qpoiYBDlSdctRGIeteCz52n4vvlwHAhWQKX/URUiRm8JYUdwCKcRvKw7SuYm4DN4nEnjA8oVNOA0zvMMtapgvuHQDX10NJ3Zwahh8BLQo2XvjFfi8uHkJRYP4OqbCULWard/8jBosdZOPtXJFXF0ZGXuxQEm/vjNOfz2wOlhXAK8ppZsm3YV3xDv8cIglkWExxs9z20i0IBQYjON6xmJLymqwqBmWFo9AK8KPZ15pmOoWHOgAcfpKduqUbNoTNEfnLkDz+eYnpXvWRj4jy5myjKUoi/QaLBBBK0G+bH+61cAgsW8bwaI4Wl1+Hqc8OlWc7a+mrmPpSNj7291kr3zO8oM+C1SASiXjo0oqTfiGiC9jeMSsLRiTz70gLn83dqaLkwbtvFsI9z1JPVA27",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-01 12:01:39",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "Interlink",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1517": {
                            "id": 1517,
                            "externalId": "1517",
                            "application": "OrderHub",
                            "channel": "myhermes-ca",
                            "organisationUnitId": 10949,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "MyHermes",
                            "credentials": "h1S6HNiK87jLSSQ9mQdgnFR3kwFgymBbpYfKOKTOqA6W2GDiHqs4GRc8HFPlDPA4quy31sQY+v7aVieuGjrewV0mV7rExhiVhpnPE2e6YOr1OVk2FS3VVfVSKKhcMy4RVBppxlE1hPgW+Mwe8WHtW10AFemp62BcTQNXsSIzfMwNJVjYpm3yZklFUMWUiUMqJAsyi7QkZUKVOY/z36k0FVgYPMjeq+WdaUm4T8jvmVXJtLJheQjpiYD8C8vFutCMC5JwCEAOJp0EPUiniz+FhkOI7b3s1U0wUt8R/aI4VD+R4JnHhohsCJHfupcz9xMQbc+3FeociXqZJJ8JLHZSRHE1g9FcBCzHHAMaFT8z3Tc=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-19 14:43:13",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "MyHermes",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1518": {
                            "id": 1518,
                            "externalId": "1518",
                            "application": "OrderHub",
                            "channel": "myhermes-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "MyHermes",
                            "credentials": "KhhFj5g00yulMijSIH+AIhJDZOKhkCOvK8bmDmK5ZiL08EdLU1JvIkoZdb+/nLDwuv78t6Mkh7zjHnj2QyrDzVBYmYp1gIP2otSCu84PvwFEOgRIEGKXIp18kwYHMBkhE0HryaoBVwYnqORH5/vhVz2rmUl3q33+6F9oeKIEGziK5vqf8TjDXJklCGCahkQe+zjY1cPzQc43pLaTI8meQ6i5Fc2NtMglKrStfE3sysmOH8Qw0aNHzDs0R6egbZvvxbvcYDl3bqk6qpllOE6dqUTYu5OSkYXN5ckY2BzyuyjgpF5Qbt0ytCFp5WhngpdcsAzPBSJsbxLi45+KvUcnBCrtlxCbS+0kzxyj470rTR4=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-19 14:48:47",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "MyHermes",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1519": {
                            "id": 1519,
                            "externalId": "1519",
                            "application": "OrderHub",
                            "channel": "myhermes-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "MyHermes",
                            "credentials": "9ss6kvrwHKcLDKIZomeQeosQVceVYY83oaK2gEN9EWvxyZBgc36LXjeFXjzCqNdZgPJova1NC8lsOmxLEQ6rQkqJQ2ORcwP61RON3qtTVmOtbRodGWI8F6Tif5l4JVwN3V6U0eYv0HeJIAZS5P+y+qiEWNteN3oMI5s6O2Z5ROpFJm4Wmtr+mWhJstqHXxzfVowEr0jgzVV9ovv/I3ovHrm2oR18pJpQ8F9hmbKlWS8Mx/tsuprfKDXHB6yY0TtY1A9rVP/yVR6idBExH0WovwBSiWH/w55ZoqTdrNMvlnIIdQ0VBFezjqlg26DQQsvfnbWToT771KD0iskq+2HTY2vwjQndkoFFQXO0aX0Gr9E=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-19 14:49:22",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "MyHermes",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1520": {
                            "id": 1520,
                            "externalId": "1520",
                            "application": "OrderHub",
                            "channel": "myhermes-ca",
                            "organisationUnitId": 10949,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "MyHermes",
                            "credentials": "CktJet0qBgjEGaDOHPUDWB6KCpZAtwnby4eUv3mGBDbA/xa91TRzfWZp1W4R16wOwB/A5EXvYZMpxQZg4XtdRDnfapKH+QpL6zD3ppFnXlwU51dBEq/X3ulpR2VUSPoxzaaKsFW7BsopLwlNMkBg6XKPiy/VBawcdGocWFgZUEuptaBhywgJaX34BV5ozh3aECMrB7P7zHfG2awMDizXerCg2zjeiSr4oTiL1ohbMMMYoA+dr5JIWrCpk+KIUSymEkjgeHS1eOSSr/XqoaZ8RrB45XVYFzIlOXEsGydlGA3VTVhCNE6E6AsLmO0pWopCSx0aQDc7oUk04KDDFaGSg/i3aFMIxxL2s1RzO+ucQ0s=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-09-19 14:53:24",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "MyHermes",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1822": {
                            "id": 1822,
                            "externalId": "1822",
                            "application": "OrderHub",
                            "channel": "parcelforce-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Parcelforce",
                            "credentials": "S4gu60+S+paiPJMW7SRwvl1pT6QI2trrbjLFXSU0W52/RtzGLT10+TbGnh65r1EL8/imaGzb67YQgbU/zDCE3v763VAP7gKfrq6ifPlHzluRaXQteGpmKzQQWPp39q6XgTzzAANLI2otAzTrQjZXYY9fUCaYdGyR2QmxOdhfZlbJQBq7cmvOHk08fPy+3DYc9sIGnOXLLpdS1rJ2apJWY03oS6d9DLwXRvfKPrwHW8mofDFl+WK4gZyRUcrlLTp2v2HrzDw9TPkqs7chL+COpbscgph4soytOYCrl/Tq2gAqjVjoC4xaUCzrbZ1RY8U/GpxFcwvJW0Gi6ZgU+4UEYLFeGa5He61pExi+bmwp2Wbase4DJjfipO4anqqwySM4iC/xjKJMg7mD8CLWoLHqzsYw4ZvrM7tg4pQo8tBVDlidp7S+DVg6nDMgogppJ4XbOLW+/62n18TvD1DJNSGLDQ==",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-11-16 15:41:22",
                            "stockManagement": 0,
                            "externalData": {
                                "config": "{\"emailNotification\":\"0\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"0\",\"sms_pre_delivery\":\"0\"}"
                            },
                            "displayChannel": "Parcelforce",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "1823": {
                            "id": 1823,
                            "externalId": "2015291000",
                            "application": "OrderHub",
                            "channel": "royal-mail-nd",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "CG OBA",
                            "credentials": "hCrj92WJJBP/ZwLM2YAsOUQpIJgNNHR1miGevkZ+ojdpUQaP02BEdQnku/fS+aZPgK+iCw4eoJuAD+2qY+FuIL3BC2jlfZCvlJ77R+bke8Mpp3/iUSFCenaWyxSbCpId4AsgilA75jx6vp9iJb/JfJ4E4ptVX+xXKkbasftz6zahmX9ZPGyQ5xF5EuPoT5jIPi+1Nrn7NwczmdUgFXYELPjeVCV3Mu4+Fktfd5u15sL0IN8N221wSo/iXsdEb/JkXxwiyyNaUJpplsdrRTF0tMRaHj5iz8NbxjzL/q7DZh5E8zHHGcCbWoQ5ZdRaUWEA8W6qF3Snxk/Q7KgHdmFYPMl82/MFAuqVgJN8JDVKSGpoxdB6Hew4iC1cibJOJYJbyTS/j8VBiAOex5jLRjwGpiX8cK+7tsWRdhcP27uX8SgZqvvBImyH9kp901/V5HYFiDGVJtd8j8zNpTVngEJ9szBrFVKQSrOvob4ZBCOLASpNP47CrYYmcYXcuO0hy1jGvSL7FFXZaYGUXFcZsZ84SxpgZR5GjwPqF0MctEs85xw=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2016-11-16 15:45:10",
                            "stockManagement": 0,
                            "externalData": {
                                "accountType": "both",
                                "formSubmissionDate": "2016-11-16 15:45:11",
                                "domesticServices": "",
                                "internationalServices": ""
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "3086": {
                            "id": 3086,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "ekm",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "EKM",
                            "credentials": "gwdBr3TbEXYietikBI+mOpc/BS8iOF8h5kCjQkIjHJQteqPeVH7Kpb2PHH8gA4bUD+nu2YnmfNKW8BNqRfvrwxX0jGIXBEBpk/BXbyvuu0KuYyoguq6K3iTbaM7awC2acUBeK5SpaRSnGYB3zODVtFY/6neMK9b5fQOhyWm2itMphSkEicN/9g6z8/Q3myo/eT7Wj1yf2SaeyA1zrp+MwzrbiVt5/800uYARkIvqqu1dYQdKpKcuHH3a5GA6MLupbPB/CPHldaWGnv2kIdNWiWz/6SVSJYI7jmru2Qnvt/mdHmFHjXXOvNl0b/bZoQaEYm9xwCPC6+14hS4bsFnFqBqoaDnl8/1PmPXoOofQ9WQI6Tuhncu0xVJIONdIN6zhIpCtKK0KylBQ5OBnusHFDUhy3F5WFX+n3K6+WbVWbNWOCqmDOdePsCWM9pTBFvpPJkmHeDeuKfu21by9Gpc1KnnwKdUcmWX+X+8kO2m0mgs9xOrlJ7+WC61TQe93w5/QIIPRmC+CjVhOawZwg16M9U89k26aQMoEWAr5PA/MLIlElw/mVdlNwVYig18fh9hPBlJMcpHh5YTFUosyj1pP7fcDImmodxyoH5GbF2elwB11sfNyALsNFvz7mDBJt9Bec3piOaS3mCGGkwbbuUMSWhbpX6Gd/7hC/ZCp0lvon2AXt/pNAPKZYsPURnShF/D/SbVMF9qDFO0Fd6o8dwsUAPFPIBvDl8MfBrqs5VKa07BqorFZ49QiOuR5jTKDAWpY",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": "2019-05-31 22:02:04",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2017-06-26 15:12:05",
                            "stockManagement": 0,
                            "externalData": {
                                "ekmUsername": "channelgrabber"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1560910068,
                                "processed": 392,
                                "total": 392,
                                "lastCompletedDate": "2019-06-19 02:13:15"
                            }
                        },
                        "3169": {
                            "id": 3169,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "ebay",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "chosen12010",
                            "credentials": "UA2QeZxpw9ep8cbz5gSaMPOI2S682+fB8XnO7q4128yBxcp2d6tlGt2M63SuwTLOnC+IZv8K4nWetiE4o5APCqm997qiPh1GnrNxcwA5b5uhhDOz2jFcSuJ/F6Yl4QjDQLgPTGwTuoVnXob88cO2d1SLEpHkGjXEtR2XOMi9RNW85FyfQpUtzdO9GmSaExjNiGhPGYeI584qaMJxCbAQ+CPLq8i+9Hyg1L7lwdPONSVsDGruBrDX3JeAXdSEV+MD34HWzyAWqqU2NePseh/zr9ZVLBzXj4tKq7su0yrpbq6knM8mCzqZKom3zxeC6rw7R5RCHDrmP1HkzvS0PpGjWz3kx6/9GJEVIMMWJ3JqglQDiWoBalXT41lbtxFOuI3140msja1qdmavPesf5ZAwHq2ryCCtl7DlRLYb5m2EJyH/mPYS0XP+f2n+DgDNXtQOtclPyR/olO68VClw+AoQHSDnqyh3Zc4WjmaLhpsDUz/5PBwp+dm1NQhTlzZiEXk2RDCScILu2ZPddMOclnrn2a4QXwQGag8AMlq7p4sBMU9MLvO073YdrzcVvxNaXnxAoaIZ1WD+W6OdA4SNjdmlNcB2gR4pNjm88paG6kEd+SGGgTTVddA/fee5kS0OtI1S/ydgGqjLsPYIJE+kTcEFc1uAOkyRVMNTN3T2zwjeleRsLTZ4PkXABDokhmO/r0mo6dxjThId+xA7Sq6Jg7MP+Tu6WveS/UbjQrtC8NicbTgxPzu5Xa9rCcK+/HyC4zwdFaCvC3MQSaGkn9MvsRcVKQklQlOISZaILV10B3/4YM5JsDmDIZr8V2hYefC7JuvVXctaiGDRbsw7Ju58vTi65dNXA0myisoNDR0cai/EvNwHszYzaocCdX5af7NXaeCxX+yitu/J5EPMmDDEiFAND9Tsf1wf/bF83bpalEpKsAaSnbvn5RE+6M8xC+oiW+At8zBLEK8SZb4fzfI5sj96Eu1qmpHTYnAUxvCzcHZmnZAlfmji6t0EfxSe0NFulzHKPPcuoUzgFFofrBhDaDXBteqMFquufFm7+vAp4XsVKgA0yAVdfi2U6niUhhw3G4D5zDbqyoKAkNDS1gnVK42sAAInZQGimQo3xO+I3nNECsbg6eFSWXwHkfgAnAq+neVZjcYm6o+qWv8To/MSw6hWE8yJc94JDu4nGKUFLlv0xE4D31sozgIYDhLggqwTAYyVIzNguxEDBo2t3cCcI1UF/+dgKXrZ3wiV4YWZJJxz9MKcLFm80P/2RITcQN9W3eVDN9wX0XPJsMYNyJp3BTbCdifrOk4hoF87oI+IeM6369uLIq8LN0x7ZHM2+GrZfXh8hSBiQnV9H19JI32/45QXaR0TU1BGVWXENy++mudI8F1ear3PDtUvxIg1kM+qHNaGah6braiv6XkYRLlH6b9YYk0BPQjoCwxyQ4HIzs5XLhr4lRoXA1rxuNeMRwV5tT6gBhsELw4vdVzKDDwhBSPb0ei/cbqc3oj9iYppLI8pwGoCQn/vBqDJaRJHoJcL9ZwE4CXFqgegU3arIGXqSFvxqlXQX6Uu+da371pu5MftsABT9j1yJSrXvfGxQrISF5SIz8gzgSHa0o5rZweOaqeq613Gwvsi8lpdMwCUX/kTGWArxeVZjySm/g28fvUgztpZGkAKcCVnSH2bzvLcvaqP6X3ezUAdfG6y+Lv/dHz3ZUFBTRGU+UN3JYDcL88vCkKrrOrsiXSVlgpo8+chOtnKjxnJFYnIWvLj4+qCBcD1NPw=",
                            "active": false,
                            "pending": false,
                            "deleted": 1,
                            "expiryDate": "2018-12-27 09:19:37",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2017-07-05 09:19:37",
                            "stockManagement": 0,
                            "externalData": {
                                "importEbayEmails": 1,
                                "globalShippingProgram": 0,
                                "listingLocation": null,
                                "listingCurrency": null,
                                "paypalEmail": null,
                                "listingDuration": null,
                                "listingDispatchTime": null,
                                "listingPaymentMethods": [],
                                "oAuthExpiryDate": null
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            },
                            "listingsAuthActive": false,
                            "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=MzE2OQ==",
                            "siteId": 3
                        },
                        "3170": {
                            "id": 3170,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "ebay",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "eBay",
                            "credentials": "YnFTJPtcN2vdvLi3beaLxVmry6ZRq1g7FaltD6+W0fYrDGon1EY6sPOLqIjyUz7LfDd4GSfFV5X3svlWdNQyFNqWO/0nvF+Hte/I+wfv0C/mKOYW3yQ2cfdiTqAcsTkAFZC7gmnS0f42KDKntDyqNqLYvfoMH60r1f5z7GFTlIeeJw0ewRw+Uw5TmveiAcb9Q6NPSycQNxnK7zeAOUvZZ4bsIVWdEwS6wX7K7oP/zjMlSdPau7+E6BjrqkuyfNIDS8Tn9xKvwmigNn2yu1tp8WbrvXXonuAlBYZcmVX1nXwdvw9sMyRQbV1Zm8HB/tR9DloBjadUybCl/dlWFWovHd6xA0d3vgXLDOVF5LpBPLFlOmaAKp24f4Aw35vR8qMW03A6+s8jJvdSBkepwrvlNTiK3RfAx7np3Z9aSBA6P2BVpUrXuUvVwFI30Ub7jLBmIjIIyTsHOIpiM+XPBrQv3g6sdm2+oPh/5k1F+M/6ZAM5Pyml+lgnqEiGdF54EXT1cZiosVxcThC8Z5cg2XmbdU2ZHqQwhArbzZ59ne1moullk19yGywWK3JVeGhy87CHqyyJGZeo1MB/DAikuW0t1Aozage5nhGfkiBzcsrRT29PVyFMGKMWCqNLJQ56dXkllbwd6HuKsxZwGTwnaqJJRWE8bRpaytOjAs9TyUA8Ojo7/+Y4T7ozK3kbP7RMrPeLFFM4rBCCJrsjaDHM3IQOlAQr9AbpOwrb7faBtC22xdXLW3l+WDo+EpulNQ2gNdiyMO9pBinfARuW9UblAJRosEUsw/tgFM9rz55YwVFQpPaMFfJe2EVVWiXsbNlIowvvDARBu2CDm9Ti9my18LHYkLq66NnKiqSwiK1r9fT8jw3nb+UtdTvLXgRIeYCkGkxLcUUGiIdtcbPLdC6U89kmNjcnoTyl9gJQ1q1WzzVGI8FWIy/YLJBGTTRy6728mFnlWrPE6JyCDAidb4V4RE5BQNLFJIY/bICprRoLNUHrIbhjiujhcU/P12NpxtY00r+FdAJmxMO1LnPl2QnNsG7pfEu093Mof25j/NkT973TwdrO/yOsd8sR9KxhzmzKHGl5l3Z2QDyMO2Kc4/mxwUfm6J1Ns/Z3K9eWjLDntck3302oC1Hcm7sVTx8xJ35sTX8VzvBBdspWsavyDs1fCfvwSKhHK4R1zpTTh4a2ZEJV+M6BroxsELe93/3mwTKHhVKK3U+xsLx7LevcRdDpIo6rcP2wylQeyRXsw+d+tw6bb7RTHL3D7Mt5l3dWDpV3KyYGzEQZao+2lm224GvtZd15Ey1FCOBVi/ks0+VYK5bOaz/bPLNDVqCW1deOujg6V0kyHm6Iy0LROngS38G2ZooN8gEaXbUdE2muq7ORC4yXgs4diVPuQo63OubPHMLctFNU/LdgSjXGFyGbzc/TBMy8nxMsijPtVbBCt3A9oUFeIubRLlP3OkXmjPrnNiLQT3NUABFOrDiIH+6DA3fNu3+8o1JDqWcOKvQhRwsRXskuJ5WSpEW39vg8M8dO/F3V6uYe2ET9t2bQhis1CuZmEFo4EMghZZE4+6pco3v+wCTi4plbk/Hf0f9MNUHA8MiGMkCW+ZsNZX5mV24UGbGHcPG+D5LyNPIeUYJTto+yomDArATe+xY1m/PNPgftBqEngESjbv06xXwL4NI+74nch7KDFuWyLLGmZxsisd6u52jybidgkjJzUFrq4fPRvDt8P+XLBjloFlBovlaOjeQGEqJ+2nrBvGBFyvYGrUxiOmXab/6B1o514kFuqz3hQeu4UZjy8Wv/Or9vv2KobqNG/SQ+9Q==",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": "2019-06-10 13:30:29",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2017-07-05 09:22:16",
                            "stockManagement": 0,
                            "externalData": {
                                "importEbayEmails": 1,
                                "globalShippingProgram": 0,
                                "listingLocation": "Manchester",
                                "listingCurrency": null,
                                "paypalEmail": "accounts@channelgrabber.com",
                                "listingDuration": "GTC",
                                "listingDispatchTime": 1,
                                "listingPaymentMethods": [
                                    "PayPal"
                                ],
                                "oAuthExpiryDate": "2020-08-16 01:02:32"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1560132005,
                                "processed": 0,
                                "total": 0,
                                "lastCompletedDate": "2019-06-05 02:00:15"
                            },
                            "listingsAuthActive": false,
                            "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=MzE3MA==",
                            "siteId": 3
                        },
                        "3243": {
                            "id": 3243,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "amazon",
                            "organisationUnitId": 10949,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Amazon EU",
                            "credentials": "7KcoJhcMl9cT5rTLVAL58CRe2hSr87TkfK9zdFb2urppxk7EPnDoisnizeXmEKMFeYlaBwVoYJG/J8rIB+MwSKJkisYeQ3Az/egy0uwOs3UE7Z/UunXuA5xMRIZLI0GkzHYH0DW/67U6/zrSNdNuJ+QXgQLiKIVp93xExHup3yGD0YLHvxNSw58/RoWrRLpL+TXpT6X9VHfigokGHrIu4uwRnyByUyQHaAyeUGBxItx/pbeldHE1T3LJoY8JiGkoQwIUYCiE4jqoLHCwXqzZ9pWQG5LFNiSaGkA7WmOmTsWZbymL7Mg/YA7DzyjuFXS1mfdDFCRN512M1fshjF+X4psCnutqb+k8UgBvEHGedBJtQrfecrFRmkovlWD0Po62JCzpJAGCKIQ6IZqioKZQLEhD4J5AMcmx+CiVHku52/5WvByALEWvk20Mu4nthBHMnrc8whDd1HNzL+Idj6r6PaebVX22E1mb8w7pUUcu20lA9LNwcBoT4VDHHXtO5nPwD4vsPAUykf5es2vuEHZfhXDN4kdJ2s0ZS85dqxK53Qc8IsI19Bifb0Z4ofOFhFApFJ+8Bn6/Dcm8KahJzH76F2cj2bSevXflDLtlbSzQjILyYP9UifJncZLriu6mHKv+b4oshBCzIl03MdWXgoLUrHhgOiGB1jctzTp92TYX0iQwomQDHudS56ZaGGDBMlJtED3F6ojBhNBZQy+vIeBTcbIBgs60H7PgAjbPACtt/nN4zlw/eOFQfUEyWW3g9lqGPaIGwZJFUZeFC1F+DmfyReyoOqOaHhe9HRYWxFVx/QDjTZReHTHtSFIsAVX7HH8lyH8XnJqVKqdIztJWmikcoyj4Djj/LtcOFlktZFRHtZZFZW9i8/gKWj5LuA0QhlwgteoF0cY8KNMqYhpdghRggN3mU66tiXl0hSHLbLsPSORRdJgi4esMT7jXzvFVXhllCqycx0EkrIbnz2HTndq9b6lAdDTxxLIEPcdObHnOAPLPWxlmrxzxxC6922DqkqlK9bmkKtrTHDuN1DrGP2p+Lf3mdrTGoskYwVJ2x89mR0QnnQtQM3jjXst/ujwZfRKtEOMNx1Cwq3uqOSsCTt434A==",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "sales",
                                "shipping"
                            ],
                            "cgCreationDate": "2017-07-11 10:47:32",
                            "stockManagement": 0,
                            "externalData": {
                                "fbaOrderImport": 0,
                                "hash": "3e474f1d119d973829736ed9f7e18a699ff607bf",
                                "originalEmailAddress": "",
                                "fulfillmentLatency": 2,
                                "mcfEnabled": 1,
                                "messagingSetUp": 0,
                                "includeFbaStock": 0,
                                "stockFromFbaLocationId": 2796,
                                "regionCode": null,
                                "marketplaceIds": "A13V1IB3VIYZZH,A1F83G8C2ARO7P,A1PA6795UKMFR9,A1RKKUPIHCS9HS,A1ZFFQZ3HTUKT9,A38D8NSA03LJTC,A62U237T8HV6N,AFQLKURYRPEL8,APJ6JRA9NG5V4,AZMDEXL2RVFNN"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1563850808,
                                "processed": 49,
                                "total": 45,
                                "lastCompletedDate": "2019-07-23 03:36:47"
                            }
                        },
                        "3250": {
                            "id": 3250,
                            "externalId": "2015291000",
                            "application": "OrderHub",
                            "channel": "royal-mail-nd",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Royal Mail - NetDespatch",
                            "credentials": "TPAJ661Zv8weflgLAfGnxOUuidrIqYz7h2VF+DXL62F97ORyRp43wkY66Xf6AidA8MPWZNmo3QiBHLP5T7/mOZ7v69O1wf07NGm/1G9TvAH8RmJXxSmk069W9MTANKOgEkaKQYpSEyRG23qfYHx5bHgg9gM9+ljgEbbfpwVJSIMo0Ve18dFSGz28O5y74k7tcmbHyFe5NWjR2imIEkvQ75Ph4Dw6Xj2yY3d5W6sc4EjiRAJ7PH/01xkQFGuVkbFIARVjk8qeMnY9qOjuqrWoRUrJEpldvcuTj6VSwjtsImEDc7S8vcBuqtuLHQACUqi0em4OSOsEUa6Uty7rodNGhArJLkhmHX5KMX+tjc+tqunrHgTk1PW4OP0gqJx3PqFKnS/DWlQ1DzNe4/OhBzwQF2+zi1ovdVJCtj5Bt+L1fpYGad82rwHa2j6mTTnQXPNGaa3uBpXKDALBz2/s/XZNIXElriH1/h+UCMupDfDiCiDJ2SeHU8J9HnraiYswLVmlUqL468lqoL/9ALeqXeLzIW0LM5lFz6df8cpZtTCV0Ew=",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2017-07-11 14:38:55",
                            "stockManagement": 0,
                            "externalData": {
                                "accountType": "both",
                                "formSubmissionDate": "2017-07-11 14:38:57",
                                "domesticServices": "",
                                "internationalServices": ""
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "3252": {
                            "id": 3252,
                            "externalId": "3252",
                            "application": "OrderHub",
                            "channel": "myhermes-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "MyHermes",
                            "credentials": "7f0kv+R/TAgt0FTTQa/jAgjjqKeSJJEqscqyXH7V3jN1DE4phocq1MLfFFYGJ0a7AqSagcRmtARNBXvSRlw3nzboLdHgfTKdaOybiEmDmID2zI1cmpNdhi3h1wPelhCWOAkoPGPSCXyndPc0AzVDWHzRte2v76B5WJM7+QuVKgxxELxEMjub5BlN/WbQhjho/rCSTfQPW5Dahflawhb8eRPGKgFq0IdymRAikIXylt0ofznpXkIgxdiqvg9duxViHxmPQ41643IDrsosKt41Bm66fYg4e2WlU00l9ryf7upXbOlhKTFpvHDEDBy/GpDIMp2uHWf10fz1QzOjxdK5YIGwJF2mO9br6rFoN/Y4AVc=",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2017-07-11 14:43:26",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "MyHermes",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "3336": {
                            "id": 3336,
                            "externalId": "3336",
                            "application": "OrderHub",
                            "channel": "dpd-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "DPD",
                            "credentials": "gQR0bS/8kMl1ZdPqxuByLRgIzhL8ifWeqzS9R90xxZP/0lQR5ZhTPJHxLRD7FfS8PLepaPkA34Oix82txMxXWBzj2oWxO58d6+NV8s7QjYwo2Nt4TcljqCBUa2q9ci2bXAAEaFr1Rmnn/Q/DfLyS0NWbPbDjknBP0+//MK0BHZaHU4sQlMTe621Bor8up9S4jQZpUJpc7uksyCJwxG8LlhzNLlOIB7bov+KWx4zfUtKs93uGnGlfoUXylNVFCCwqJBJkyB++HMZIY9HPslnQ4doB8U8zwTV0zcu3hUdCWahbeEPSR8/zIOQn9GyOftzEWqa/3qB6VwLkg1DbtVU8DyCIbhcrzLaz9sOkl9XnMid+ZT5Gp3+w0auL7svxqiKSjmDvG81uifFbJxZL23Xk8EBpl9Sfy2/kTwpVlOlB4sy0Mm2zN1HeiJXb54tPBt7plNPYFWtxF83Ij53uL+cDpPzot8KqlK4DPQ92Yr6xRqoAqY1Vsw5okylz2k48Rw8q7sipDQYuza+A5v8LMjGVQzpl/gl8rh/WtcNxvyL+D4vSWafxx7GfIglUfschK9EZgxp1pC26UWBt7B41zQQIaSqKRmiCKkN8ZxrbQ5TyHUEY8LlZwJBwGxEye2qW6mL2",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2017-07-21 14:20:27",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "DPD",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "3337": {
                            "id": 3337,
                            "externalId": "3337",
                            "application": "OrderHub",
                            "channel": "interlink-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "DPD Local",
                            "credentials": "VsgnLNrzpX0lbxDf762CWR/zHY4J57fFZIPPMuHBWSs7q1woeJfGIhJNV3Q8aJufgNlA6g9ipRofQjMMT1GcGEcus5TQYzLrMag3PekIzx/LeW9HxYeJlS6+mVJ6pNki09+ePPLJfUhkZ0DgTghmh0Vk2/0Qst1ur9IvRmdjNoqDtCF9avYJlq3q2nxxf8erS1x6QwMe6h5BM4vOwJc44bt92/ioK6q+KaUJYrbEKDTv+X9skJuACyDVkrdC2o3KquD3ie2hKWfQ1BeXhlfId1WmO0KXFKZpn0bVAIsYPadjlubUmIxBw0IZ2vLMkkVQeJnmIIdmQ1ZDpD7YvZWNN/wYzHavIBa6+0UEeCfr1AlSw0bhg/F/ZWn4j77njvalqMbDQjFaiTALO2NofS3htvF4RQZVwEYXEKwtYZyaiRKotIY1a2tYAWOQmVJOluTPZfwD3WTlvB26xfE6ENiK7sgLiE6/dbrN4xTjAsTAB6d2Z0Hxm1Oa0xaUIy0/Pyg9oUUCkwzAlyP/dpXbtMUQiLbJxJg9uVMLw44lBf8WXVz5KcJK1/Hw8ZDfPWMz+dIj0uInuaA0kVyxz/xx8IUKSf21a03DibXKRGKDM0WSlhAO0+1gSqPQPLkw+srgAg0sRmzIXBk5rpq8opXd1QLv4ugYvkGs7rh+OU7mcl0rNIHIT+m8Hpn3SDeW66PYhnxqtmX2PAJ7K+jDQaKxmCguEA==",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2017-07-21 14:42:40",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "DPD Local",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "3747": {
                            "id": 3747,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "royal-mail",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Royal Mail (PPI)",
                            "credentials": "Royal Mail",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2017-08-24 15:38:05",
                            "stockManagement": 0,
                            "externalData": {
                                "PPINumber": ""
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "11660": {
                            "id": 11660,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "shopify",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Shopify",
                            "credentials": "2nfg+u+z7qgiHaUqL7yEp5wBIVm2mDW9bW8IRa/tbJ/NVwBsRfGfZD3QNc4CHhkjidsA6bUMGlGTIcVhdvsB+yEecd65eRhg82xhJ6Phmwg51zsVENmCtRvuQ2tjJGpibW3M8gGAW4IJ+5eAdJbvG9jT9+OqlLLGVK4FSZ9+iQoHjKsQ6DqoQd892BOl7dFkcKLmSbKEAXQQXkRU0D9sMbecSmACoa0CSfBCGTEqgOE=",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": "2019-06-26 12:25:37",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2018-01-04 16:40:43",
                            "stockManagement": 0,
                            "externalData": {
                                "shopHost": "dev-shopify-orderhub-io.myshopify.com"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1529671637,
                                "processed": 1,
                                "total": 1,
                                "lastCompletedDate": "2018-06-23 12:47:32"
                            }
                        },
                        "12354": {
                            "id": 12354,
                            "externalId": "47fwg8cpdt",
                            "application": "OrderHub",
                            "channel": "big-commerce",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "BigCommerce",
                            "credentials": "nYg4OGzKeMmDeVdlbKJRBg3WLTlxIWOK0or+WcNloytYBe91o1iu4DDfhTuCiat72+b+3G8yCA32SCXdB2+mzBxnW2mkSfUq73gHOKhQpNkBiA5dT+XYfHokcdHJErxWps+QtofTX0zFmINbvZOvmAvPpLdUblNMW6DI7IJ2wHoGFlo0UjB4b1PIZJ3HbgDZRD3RQWCIHbXgxpuOAKAJx6dHVtnx6hxqVGMrXB2CApU=",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": "2018-07-30 15:12:37",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2018-02-19 11:20:51",
                            "stockManagement": 0,
                            "externalData": {
                                "secureUrl": "https://store-47fwg8cpdt.mybigcommerce.com",
                                "weightUnits": "kg",
                                "dimensionUnits": "Centimeters"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1521127671,
                                "processed": 13,
                                "total": 13,
                                "lastCompletedDate": "2018-03-15 15:28:15"
                            }
                        },
                        "12355": {
                            "id": 12355,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "ekm",
                            "organisationUnitId": 10949,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Acme",
                            "credentials": "Xu6vYvSef2w9DAA3fiZvhLYknLkLPyN9lGehw++yU23TAPfkgFESdOvWztrilx4/twu7etv1q/3xrxpQ2ZqZ6lE9MqsxovvQCRYZRTD2anY08cuzlDNJ5Xm7cds/SPcuA0bLkbkeO2SFgVqrc7cF4fJYfO/FLQOY878LYaTvFJL9xT8jx93gzf8TGDctB1IABpkLG3kaZ/7t1gD5adBukAbhzu9CA46r0YyqU4rDqFTGDS2BVp1z/p31ZFTElA42nRsHJdoJ+Q/ICfjLfD+NlELsRWne1dp4Y0x7FzZ6djfcS/ZvtWoPexv7Xz0VcGdz4Bz9odLqI50TOFJ+GPVOeE/XX9k8Hk9Yx2P/j1R082HZpK/NZlKdCD8ovh/g0oO4dSaZNYpKoZs3uUWogN56GvpQlxUf1CtorODaTTwBPPbSpSAEzzbtMfzJJSdbot6lM/hOyurtuwEAmao+V4jZwX7Pqq5DqrTXVZcHTtSUZQ2BXOP6W0ug07rcnXAXAd9dNcmld5d6ywHFhO1U3osZYrbat5niVRORBdmAu3842KhfuHefTnZ9D34H2YpqcR3wVsEd44oPgSY3EJU9n/lvUj/Aohn0Oz+uoOXlXPCiNBl1wQQ61CZSRsQqCF4tWgDdQsUb0wEJJaUSJ/JcnFBiZyEeTk5TnghbxhXSpSvuDcBNgh2cRUhal0mf/8+zRY8rEymTDzRxXoUd9InS3YJS64dfwJvJ4OOmq0bI1BXlMXs3IX/XAzS/Cyj8oAcw3eIx",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": "2019-05-31 22:01:11",
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2018-02-19 11:37:54",
                            "stockManagement": 0,
                            "externalData": {
                                "ekmUsername": "channelgrabber"
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1560910676,
                                "processed": 392,
                                "total": 392,
                                "lastCompletedDate": "2019-06-19 04:34:37"
                            }
                        },
                        "12628": {
                            "id": 12628,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "woo-commerce",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "WooCommerce",
                            "credentials": "n74zEWIEcNi2aN1IiseeY4EBTnZxbT+pIt/V6XmdNHJSxK/0SPaiiSWKFbV0qSJtk88YwMaTf2wIgN+32hsCPIertpodyhhccXY1D5C72z07zEe5R48fxaRtPOcZDdbcwQzKbdz6qMta9o5ZyAlsVqYmezGrQ+tugX0sx4wACg1caskGqzGSjsrXMsSzTeG5/S7B1kT9qhXXE7vGBks03Q3l1RK2lbdd1ilO1WorAQZVtZuCugBuPuINcADQ7RhkqloG7UALR5QuF3oTdofh5ZrqKwx8c0FCQErZKn5El9iWO7NvgaHybiizYrIPDUoAacRxpJXx8Z4BjlSrItmwlIMC1XPr/jzOh9CVU/9i0Vo9BkoOpHGXP0ykzP2fHdw1hRaV3UbnEe7QnR5Oqf1t5wdfCNOuVEG3cNTJqSY87l+6XBtN0918lq1vT8p2A5n56GuKMRsuzrb5afgNAfDmXA==",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "sales"
                            ],
                            "cgCreationDate": "2018-03-09 14:20:55",
                            "stockManagement": 0,
                            "externalData": {
                                "decimalSeparator": ".",
                                "thousandSeparator": ",",
                                "dimensionUnit": "cm",
                                "weightUnit": "kg",
                                "taxIncluded": 0,
                                "currency": "GBP",
                                "sslEnabled": 0,
                                "sslUsed": 0
                            },
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": 1555459231,
                                "processed": 92,
                                "total": 92,
                                "lastCompletedDate": "2019-04-17 00:02:27"
                            }
                        },
                        "12917": {
                            "id": 12917,
                            "externalId": null,
                            "application": "OrderHub",
                            "channel": "royal-mail-click-drop",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Royal Mail Click & Drop",
                            "credentials": "FyGQTsTo6FxxEgfZP5u/mg3S1GYllTb1Fy7Rs6Va50hJKMOPelFoKHpygFXmXHysCbIR9GpAjIdCpopxwHuwvFAe5o1azYz+WlSKG8VGPJuDDFPhZ2dFPlW2s8DScHpjFO2TnH2D+7DmauR1W/Ttm2v8FIoWY2Go7+S+GE3fq1wZmf3eESt84Dn8hsx39lzz",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2018-04-03 13:25:10",
                            "stockManagement": 0,
                            "externalData": [],
                            "displayChannel": null,
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "14098": {
                            "id": 14098,
                            "externalId": "14098",
                            "application": "OrderHub",
                            "channel": "dpd-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "DPD",
                            "credentials": "uUXAEpiPi895LUMm4TvfhhQDFNZUt6mMfjkRyqEKtBODeuPnx2f5EmQUhp8AQmYbMbyM6B7amdpHW0alXAG7gt/ufw1Ndhb+iK/q8if8V6pNK49HGba7f1jY5/0d9+QEN0TAeadR0eSZeawW1BweyvpGx1b6sLF21ejgecRKHPtFPniv/Ym6EO26D9OSyyZSIZygyintBBX7r9fnCiCA2BRk/IR49CrdYTz5jeEubd8ARsY7MJXZjE6O6/TePqYKzXio4Q+GnA2i4Tc+dt9WbfaiRHQjpT5daot6wDEWeIDtm2fe4w+f44uuitY9S5zSVdcMFO1Piu7LPk6ohoebtoK4OQUZpQVJDMNlO8vgX4zbgT1GxYzDx1FdVtniKnp6eZbt3rp+2h3WNbN27w3NoMWQa9Lo9SHaz6zHgnhTveTFQ/oW142T1n3wEzE4qKAurT2hOix61b2uUO0wP9R95b2v0ryLxLRjmI2KX2pp08UBa9VAgAh22fI1KhK38LVigCE+0doPUcEnF0FeogihMdtC9ZFRP13az2+sfw/p+STwbCpujEdfg4qpDLRAkE0saEUVBBpzSA4ipunEm++PrsqDv2jXlMGeP+ViUD+m/BcW5von+M/d3q9JsjT7hzak",
                            "active": false,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2018-08-23 13:00:52",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "DPD",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        },
                        "15504": {
                            "id": 15504,
                            "externalId": "15504",
                            "application": "OrderHub",
                            "channel": "royal-mail-intersoft-ca",
                            "organisationUnitId": 10558,
                            "rootOrganisationUnitId": 10558,
                            "displayName": "Royal Mail OBA",
                            "credentials": "cbwMXR2z3oeu91Jcqi8PbLZwsbe6mRSmKotJNhSP740WFyH/xN+I8AzruE/RgDS0urTeMdRLX/EaaeVaVszk/SnJVyih4VJyOmskqrzzGVrA5iaPGM1nLcY4rwzZZd9DFNLYqEoSbqgJ141orsmCLVL2ApOLY7SpfePZywCmFnKlTJ3FDsSLix4kX0wjuIWi3QJYAMFahOrb6RXR24BVijZ/x9mniiKUBkzJZccDuvGcpRLcNWgnl//d/Hspnum7fmCjhcVZpeKcTjyCPlNKyrbwqtYYCyiADvh4zzQt1rFPR4DlSE6z87M85yPTiAr/1UWTohXOrE/vOPSZmxw5cOg6Y6weDvi2GDtu4CZvR2+Q3k89NyyAIT/VLGMugWmJKVf+aibk6dpd5E4URa23CA==",
                            "active": true,
                            "pending": false,
                            "deleted": 0,
                            "expiryDate": null,
                            "type": [
                                "shipping"
                            ],
                            "cgCreationDate": "2019-04-18 09:29:14",
                            "stockManagement": 0,
                            "externalData": {
                                "config": null
                            },
                            "displayChannel": "Royal Mail OBA (In)",
                            "orderNotificationUrl": "",
                            "stockNotificationUrl": "",
                            "stockMaximumEnabled": false,
                            "stockFixedEnabled": false,
                            "autoImportListings": false,
                            "listingDownload": {
                                "id": null,
                                "processed": null,
                                "total": null,
                                "lastCompletedDate": null
                            }
                        }
                    },
                    "stockModeDefault": "all",
                    "stockLevelDefault": null,
                    "lowStockThresholdDefault": {
                        "toggle": true,
                        "value": 5
                    },
                    "stockModeDesc": null,
                    "stockModeOptions": [
                        {
                            "value": "null",
                            "title": "Default (List all)",
                            "selected": true
                        },
                        {
                            "value": "all",
                            "title": "List all"
                        },
                        {
                            "value": "max",
                            "title": "List up to a maximum of"
                        },
                        {
                            "value": "fixed",
                            "title": "Fix the level at"
                        }
                    ],
                    "taxRates": {
                        "GB": {
                            "GB1": {
                                "name": "Standard",
                                "rate": 20,
                                "selected": true
                            },
                            "GB2": {
                                "name": "Reduced",
                                "rate": 5
                            },
                            "GB3": {
                                "name": "Zero",
                                "rate": 0
                            }
                        }
                    },
                    "variationCount": 0,
                    "variationIds": [],
                    "stock": {
                        "id": 6945862,
                        "organisationUnitId": 10558,
                        "sku": "EXWHI",
                        "stockMode": null,
                        "stockLevel": null,
                        "includePurchaseOrders": false,
                        "includePurchaseOrdersUseDefault": true,
                        "lowStockThresholdOn": "default",
                        "lowStockThresholdValue": null,
                        "lowStockThresholdTriggered": true,
                        "locations": [
                            {
                                "id": "6945862-464",
                                "locationId": 464,
                                "stockId": 6945862,
                                "onHand": 2,
                                "allocated": 1,
                                "onPurchaseOrder": 0,
                                "eTag": null
                            }
                        ]
                    },
                    "details": {
                        "id": 1888931,
                        "sku": "EXWHI",
                        "weight": 0,
                        "width": 0,
                        "height": 0,
                        "length": 0,
                        "price": null,
                        "description": null,
                        "condition": "New",
                        "brand": null,
                        "mpn": null,
                        "ean": null,
                        "upc": null,
                        "isbn": null,
                        "barcodeNotApplicable": false,
                        "cost": "0.00"
                    },
                    "linkStatus": "finishedFetching"
                }
            ],
            "product": {
                "id": 11400129,
                "organisationUnitId": 10558,
                "sku": "",
                "name": "Excalibur (stone not supplied)",
                "deleted": false,
                "parentProductId": 0,
                "attributeNames": [
                    "Colour"
                ],
                "attributeValues": [],
                "imageIds": [
                    {
                        "id": 13812565,
                        "order": 0
                    }
                ],
                "listingImageIds": [
                    {
                        "id": 13812565,
                        "listingId": 10222599,
                        "order": 0
                    }
                ],
                "taxRateIds": {
                    "GB": "GB3"
                },
                "cgCreationDate": "2019-05-03 09:27:57",
                "pickingLocations": [],
                "eTag": "8f8fc5df0ebb20e1c3f34c66464a8689cc6128c2",
                "images": [
                    {
                        "id": 13812565,
                        "organisationUnitId": 10558,
                        "url": "https://channelgrabber.23.ekm.shop/ekmps/shops/channelgrabber/images/excalibur-stone-not-supplied-103-p.jpeg"
                    }
                ],
                "listings": {
                    "10222599": {
                        "id": 10222599,
                        "organisationUnitId": 10558,
                        "productIds": [
                            11400129,
                            11400132,
                            11400134,
                            11409247
                        ],
                        "externalId": "103",
                        "channel": "ekm",
                        "status": "active",
                        "name": "Excalibur (stone not supplied)",
                        "description": "Wielded by King Arthur!*<br /><br /><br /><br />* we think",
                        "price": "2.0000",
                        "cost": null,
                        "condition": "New",
                        "accountId": 3086,
                        "marketplace": "",
                        "productSkus": {
                            "11400129": "",
                            "11400132": "EXRED",
                            "11400134": "EXBLU",
                            "11409247": "EXWHI"
                        },
                        "replacedById": null,
                        "skuExternalIdMap": [],
                        "lastModified": null,
                        "url": "https://23.ekm.net/ekmps/shops/channelgrabber/index.asp?function=DISPLAYPRODUCT&productid=103",
                        "message": ""
                    }
                },
                "listingsPerAccount": {
                    "3086": [
                        10222599
                    ]
                },
                "activeSalesAccounts": {
                    "3243": {
                        "id": 3243,
                        "externalId": null,
                        "application": "OrderHub",
                        "channel": "amazon",
                        "organisationUnitId": 10949,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "Amazon EU",
                        "credentials": "7KcoJhcMl9cT5rTLVAL58CRe2hSr87TkfK9zdFb2urppxk7EPnDoisnizeXmEKMFeYlaBwVoYJG/J8rIB+MwSKJkisYeQ3Az/egy0uwOs3UE7Z/UunXuA5xMRIZLI0GkzHYH0DW/67U6/zrSNdNuJ+QXgQLiKIVp93xExHup3yGD0YLHvxNSw58/RoWrRLpL+TXpT6X9VHfigokGHrIu4uwRnyByUyQHaAyeUGBxItx/pbeldHE1T3LJoY8JiGkoQwIUYCiE4jqoLHCwXqzZ9pWQG5LFNiSaGkA7WmOmTsWZbymL7Mg/YA7DzyjuFXS1mfdDFCRN512M1fshjF+X4psCnutqb+k8UgBvEHGedBJtQrfecrFRmkovlWD0Po62JCzpJAGCKIQ6IZqioKZQLEhD4J5AMcmx+CiVHku52/5WvByALEWvk20Mu4nthBHMnrc8whDd1HNzL+Idj6r6PaebVX22E1mb8w7pUUcu20lA9LNwcBoT4VDHHXtO5nPwD4vsPAUykf5es2vuEHZfhXDN4kdJ2s0ZS85dqxK53Qc8IsI19Bifb0Z4ofOFhFApFJ+8Bn6/Dcm8KahJzH76F2cj2bSevXflDLtlbSzQjILyYP9UifJncZLriu6mHKv+b4oshBCzIl03MdWXgoLUrHhgOiGB1jctzTp92TYX0iQwomQDHudS56ZaGGDBMlJtED3F6ojBhNBZQy+vIeBTcbIBgs60H7PgAjbPACtt/nN4zlw/eOFQfUEyWW3g9lqGPaIGwZJFUZeFC1F+DmfyReyoOqOaHhe9HRYWxFVx/QDjTZReHTHtSFIsAVX7HH8lyH8XnJqVKqdIztJWmikcoyj4Djj/LtcOFlktZFRHtZZFZW9i8/gKWj5LuA0QhlwgteoF0cY8KNMqYhpdghRggN3mU66tiXl0hSHLbLsPSORRdJgi4esMT7jXzvFVXhllCqycx0EkrIbnz2HTndq9b6lAdDTxxLIEPcdObHnOAPLPWxlmrxzxxC6922DqkqlK9bmkKtrTHDuN1DrGP2p+Lf3mdrTGoskYwVJ2x89mR0QnnQtQM3jjXst/ujwZfRKtEOMNx1Cwq3uqOSsCTt434A==",
                        "active": true,
                        "pending": false,
                        "deleted": 0,
                        "expiryDate": null,
                        "type": [
                            "sales",
                            "shipping"
                        ],
                        "cgCreationDate": "2017-07-11 10:47:32",
                        "stockManagement": 0,
                        "externalData": {
                            "fbaOrderImport": 0,
                            "hash": "3e474f1d119d973829736ed9f7e18a699ff607bf",
                            "originalEmailAddress": "",
                            "fulfillmentLatency": 2,
                            "mcfEnabled": 1,
                            "messagingSetUp": 0,
                            "includeFbaStock": 0,
                            "stockFromFbaLocationId": 2796,
                            "regionCode": null,
                            "marketplaceIds": "A13V1IB3VIYZZH,A1F83G8C2ARO7P,A1PA6795UKMFR9,A1RKKUPIHCS9HS,A1ZFFQZ3HTUKT9,A38D8NSA03LJTC,A62U237T8HV6N,AFQLKURYRPEL8,APJ6JRA9NG5V4,AZMDEXL2RVFNN"
                        },
                        "displayChannel": null,
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": 1563850808,
                            "processed": 49,
                            "total": 45,
                            "lastCompletedDate": "2019-07-23 03:36:47"
                        }
                    },
                    "12354": {
                        "id": 12354,
                        "externalId": "47fwg8cpdt",
                        "application": "OrderHub",
                        "channel": "big-commerce",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "BigCommerce",
                        "credentials": "nYg4OGzKeMmDeVdlbKJRBg3WLTlxIWOK0or+WcNloytYBe91o1iu4DDfhTuCiat72+b+3G8yCA32SCXdB2+mzBxnW2mkSfUq73gHOKhQpNkBiA5dT+XYfHokcdHJErxWps+QtofTX0zFmINbvZOvmAvPpLdUblNMW6DI7IJ2wHoGFlo0UjB4b1PIZJ3HbgDZRD3RQWCIHbXgxpuOAKAJx6dHVtnx6hxqVGMrXB2CApU=",
                        "active": true,
                        "pending": false,
                        "deleted": 0,
                        "expiryDate": "2018-07-30 15:12:37",
                        "type": [
                            "sales"
                        ],
                        "cgCreationDate": "2018-02-19 11:20:51",
                        "stockManagement": 0,
                        "externalData": {
                            "secureUrl": "https://store-47fwg8cpdt.mybigcommerce.com",
                            "weightUnits": "kg",
                            "dimensionUnits": "Centimeters"
                        },
                        "displayChannel": null,
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": 1521127671,
                            "processed": 13,
                            "total": 13,
                            "lastCompletedDate": "2018-03-15 15:28:15"
                        }
                    }
                },
                "accounts": {
                    "844": {
                        "id": 844,
                        "externalId": null,
                        "application": "OrderHub",
                        "channel": "ebay",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "Calico Trading",
                        "credentials": "iCpnvOwePsMJq7J40bqlO77erZ5X+00dzKvuRk4PpSGCEsEYCixvrALXTh8lZ4anCsePIJMLRFc8MN0C2DNL7WWBffE20qfU4ZmfY6BtJjLVpXn3Y8/aLas6uI7BYX+xydtAavkSmiGJOLPEtQZqCpHT008zPFTA59ebB4tDe2DvZHIZAPoxMX+QfpaaujyBxpzw3RGmId4C6LzUJ2G5meV8tzw92/SMU5alnWCrX+p1LUK3tk7CJRFDU6PSOn8Lh8ZegQEAoMUGMEOCZuIvhopDmYiCm2PLvk1f+IofZXTufQtAjZBS5yyDTVqqKSS056zp02tyh3J0aATDFpVONkJ3IaTFRNpH0eG3nwwsI0RgaRPTNVr/c2Nhf/KblTE0P8iOus8UJZTIesgXQApt2yvUr/P/X/VD0gkXZO/nREmdRqAerC1Usx5mCLvAUBYoNo3el8jsdYFX2ykzbwFd0cHJGaQPujEdjmR4ELs/llTelUGT6v+MIrfw9cZQ8SrP2OziAP5lsrr9tqi9xG45dGas+/jCOWdU8eAxm5rcQEtDlWG1Kk74tbwWFLqMgrKIVE+yx5Xtud+cKgEp2IDD+4bc/7plEJBW0XQ6nMJPelfKq4DnQe4vw0hcgyJjAzJFyDQtN0xVlOmciVHRi44PTgEFKTVUmwBzwvxsNeUR1an5qeZ67gOxRHgndI0QVq3aKI8vm8+1arW1Hg7iYYbdoZ0L+Inl+SGRdQFVwfvgmLjV9YacJV4o/m2X/RUawj7i386r1HSitafwnICDgsOk/psvSb7phj4Z/2jxx+E5VjlW38v6bVpk6UYuGimbVyh9gqNGq3oX1rRPG7jAiUQTGIoSFt56BJFAEyDMXqNnzv3c/nYm+gTw40pmhPPAUMb30ZMecHdIG5ulqgaQaxADOM3Lc4VddBhFO9ejdIrACf+Az+TI4dzGgDnh/62yrS6hkdP5qR3N1LUQmyEgDH386oy7uQsoe57Dnuy29YNI9ijjC/3Zlf0k/O0SzqFCKGDOTOWPDA8yj5bw4ZnwyWE0Sl7FF3QshrhdmMlJ8hZz6oE8M3J8ynNPNzHl4k+ItplWSp+tnOgMv8r6CZ3/jvW1vfUQ1z2kzw7g8dt6NcQjFTbCAriDdhJPYTgeOtDRwaWpHuFrezA0suhYNVo/7CUyGzkOk1XFpMazNlBUKDFHFwGAHLMRLTKivg0r/8pQzoqROxUjDedGs8YXQNRAkQzdQx2cTEwW4yJNrEn9j8nFD+84l5j+xKTQfwkbfQ0AzVBO/psTYA4PAZDArtxqxiTroiMNdaZ3P8vXDpojkardR2QKsQEEoInXaGHpNzxLVdnrZcbRBCZMaWacecUH6H7vE41PAnslbm6E/0h1gCHK2tqYCLH1M/iYTL/hp64nPlPyCb3P0/TGu/gFcamxSRqPF4cP/MnENAtgIW9UxRsEEUbMVSvYxg9MtkADggF9pmL2L4Crkj+FbTZ7+yhRxhU2ycwbhZzoEXDOqPauxnDEXIbXlV0gJrUnhwIcA0NQi5JkyZukM3HjvWX4j/MB1mFsKlA0wdfVYmh8kIFr6bLCfjuipbC/sUIB/93U+rvSGiaVNqM52w6dJjIQZ+p9eDJzKyHy5JIipPRhCcMpBx5xnUA9rlwhOhy9wKzxRfUQApXOPu2MavivSO/8cP5mLdkylbH3T1vBBcuSVcHhQ+Wvhpd4R1zIAt8EtZyfSJgsiw3EsQHXfebAoKffXQNKX63T2bXJi4WAOrRYjPAsey+YmHk=",
                        "active": false,
                        "pending": false,
                        "deleted": 1,
                        "expiryDate": "2017-07-20 12:16:35",
                        "type": [
                            "sales"
                        ],
                        "cgCreationDate": "2016-01-27 12:16:36",
                        "stockManagement": 0,
                        "externalData": {
                            "importEbayEmails": 0,
                            "globalShippingProgram": 0,
                            "listingLocation": null,
                            "listingCurrency": null,
                            "paypalEmail": null,
                            "listingDuration": null,
                            "listingDispatchTime": null,
                            "listingPaymentMethods": [],
                            "oAuthExpiryDate": null
                        },
                        "displayChannel": null,
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": true,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        },
                        "listingsAuthActive": false,
                        "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=ODQ0",
                        "siteId": 3
                    },
                    "1096": {
                        "id": 1096,
                        "externalId": null,
                        "application": "OrderHub",
                        "channel": "royal-mail",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "Royal Mail PPI",
                        "credentials": "Royal Mail",
                        "active": false,
                        "pending": false,
                        "deleted": 1,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2016-05-25 09:55:08",
                        "stockManagement": 0,
                        "externalData": {
                            "PPINumber": "HQ12345"
                        },
                        "displayChannel": null,
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    },
                    "1445": {
                        "id": 1445,
                        "externalId": "1445",
                        "application": "OrderHub",
                        "channel": "parcelforce-ca",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "Parcelforce",
                        "credentials": "2+p/GVQs1ndg/heKwGT6bePmrr9ElapPzIhmSYdggFDPypxFY+/sIyYl5nWNhBpugPdB/rWFnyon41Trir9I1tPLadwkG3tx4nXqeN1Fs417/NKHRZtZw2pYcLAOYiJO5egBD/wtYAVOWwTie99HiBsOXxjuOifLQ3/eoo2lgorjmnQeRJ5sKY535YOsHS3m0F47C2ypo5emUIw3pXCoSncxdDydOmrY0H5tJLUIA9nGZ7DDuNBQyfFuu97XsIExuriMw3qIg9MXPcAFy56silpxXdE8qMAlIN9NNJQqlcSOt++u6XpoeO6FEHXmvc/186H3Pi/XXwp/xpr7+0Y8FK6K0/rPga17hGWRLY+AidVnNyYl7qc1LljcEmhSXD58fpzMIOcH6XRjiV/giHHZ4EqTKBMIBpxwJ8fpqpJAGAlGs7t05vol/44LQ37cVzNp",
                        "active": false,
                        "pending": false,
                        "deleted": 1,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2016-08-30 14:27:11",
                        "stockManagement": 0,
                        "externalData": {
                            "config": "{\"emailNotification\":\"1\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"1\",\"sms_pre_delivery\":\"0\"}"
                        },
                        "displayChannel": "Parcelforce",
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    },
                    "1447": {
                        "id": 1447,
                        "externalId": "1447",
                        "application": "OrderHub",
                        "channel": "dpd-ca",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "DPD",
                        "credentials": "ljkIEIyzleoeSE6GrLzXJXh9nlRkVmiWu+tEm023Qsld2iu0461qm3LK9ZmwxZ110Jh/PBp8E1hUuUd61B/7cei8QWZcF8qjAq6IyZnkL+MygqJrScSdbowuiFSJfsw2oKiNH5pkLZ37HMyi/s4bNkCTOCzNIF+QBeWDX7GEXwXAkBhMGUIrQcXrjvf/aJV6+9D2Wv3TZqXRrZHg8HYqL7KJm1f9FGQ5H6Fxsn5Ams7+qTcTfV4nxKB7mM2aQxLbPF2rz0B5UU4kKQgLjc6p6ISTm+HRkEPqo+TQMZU9diBQOlrEm5MPBDK/y/QKZf8SqtEG7L3VKSw5pbpyThRUvcEeWsq5eW+r3zQ1bhzOewYHHD3psQWUlWlWC2+ERO430xrYDiihs5gOBhtG5rYI15g5Hz7GrRSPXTJl2KHeOrwTUnKVdmgOTYFBNwiXB9yHAMw79394xLhEpgeoZAon59z+n/kgCV+xf3164Up2DNB4ZXeC0bKCwZS5UU1aqGV8imcBrsh45MlaF/jDeRI+ZoWhOUjGdJZrqibPhAKnOG0PW4028tQ7WUwl1Q8qZ10AQRqQMTIChoiTVr/CYJ+P+fW0redHDDXzi2jSa4sp9sPnsmkCIP0wuOkZU3yxawpi",
                        "active": false,
                        "pending": false,
                        "deleted": 1,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2016-08-31 10:08:30",
                        "stockManagement": 0,
                        "externalData": {
                            "config": null
                        },
                        "displayChannel": "DPD",
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    },
                    "1448": {
                        "id": 1448,
                        "externalId": "1448",
                        "application": "OrderHub",
                        "channel": "interlink-ca",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "Interlink",
                        "credentials": "NhqQH5Yvo2mTPMAitLN8XOZRGJjNKbu4ld0zvfLH195fntGbuiTG++69OzEB8uB0xUkNuWl26t4ou+Xl3A3pG8Nj4doKnuE7Tnztrn82wVrGMkJHElVGs06ffZdFvG0s2MKehldhazxC4ycuEbjDX/AQZkOTULoat+XeDeujvZnN2xxB+o7xpx5FOjeJTyyypjoFa8MEtGQovHpCPYO7ph/Av7MU15q9doddvrARtiIEK987xXMSnei8Q+eauGWUs+74javCusSj0u5QKPLOoph/iUNtsU6XPuMgSbUvaNpQBIR4HVo/ztnXlOx8JeNC+TrnBQX13N+5I267uYhMNpZyh3I4jj2IE44WeJvWCCBCw+68U3UR4DMccBKx5ClJkReekIwl9D9KNO+dK1lEjL62B9peb1JQ+RgFeulo1XB4otF/cJXD9MeliZVDF8P2rR3v7QhyHfpMPQdOu8w2/blnjpu1PUdUPQhUVACqPNJjYpLLSeSWZjZaTENJs/lTTmOSUAMYMsVwCNAWQL8zpgxNvK3PmtStI9g4uNhRPUUgt1d+L+Pu/wSqkVhqQ24YbahGfPHKCC09QODqBBkgaHk0IlcVIsKLOJ5efJBCP79HOpeN5ZvZpBhhni+yAhDxeRlk996cQJGl85xiVHGgU6Tf1KycBa+SWeKj+y90s1aKVU5yLhEJL+DNeq4vXHWMt5KvQoA2si8GMUoKzDnP1w==",
                        "active": false,
                        "pending": false,
                        "deleted": 1,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2016-08-31 14:28:29",
                        "stockManagement": 0,
                        "externalData": {
                            "config": null
                        },
                        "displayChannel": "Interlink",
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    },
                    "1456": {
                        "id": 1456,
                        "externalId": "1456",
                        "application": "OrderHub",
                        "channel": "parcelforce-ca",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "Parcelforce",
                        "credentials": "X5d7L4B6DUXntVIsKEC6J7ULjviSYN9GsxICofFbFW6PswrPlEmAdeK7IU7ZjFrFRTPaP7W6e/Iz+jG+KqKQNCLVF+B2ggau5v22zwx4KGTl1+9TYhkfhGHXhA95m2l5sVBSNOiSr9ly/kprrBXa7l22ouWiXYXt2Fzcx2VbDjYd4zAIN1Tp5N80alyfkRzVM/RoQJ9IwFVoFHqMXE2FVPUz5VAriZ9LM5DTJHUYuS2bZ8d+s8c4BOzrwi7NZhEzzsbWtDF9gKNRKc/wqKW3idSMPGvSJTnjCGMH9+7FxHXhYN9BE/igqnluhIxUHttJ7A4FQw3yEypyDDybfJzta54pGULumsMmqkBSOZ69YgKCrYpgxfZdhfnzmy8hIiAwoTOZVsgQbBP4rcbFyyD/O+pXGuVh3IDeclenPbv3i0jMu0SsVFDwI5QcDoostNQMbhCe/+nuTvREI1p86aJyAA==",
                        "active": false,
                        "pending": false,
                        "deleted": 1,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2016-09-01 11:32:39",
                        "stockManagement": 0,
                        "externalData": {
                            "config": "{\"emailNotification\":\"1\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"1\",\"sms_pre_delivery\":\"0\"}"
                        },
                        "displayChannel": "Parcelforce",
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    },
                    "1457": {
                        "id": 1457,
                        "externalId": "1457",
                        "application": "OrderHub",
                        "channel": "dpd-ca",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "DPD",
                        "credentials": "fG7mb7o2273LKUcn60vpLqfgxMp0G7LArSWNJBhjacburSEe4dIIXIwv33UPngs02UrfSOtf6YxYLQ6+efXNa6NbF4r1WlcVlbsQq4kBrkPtHWnfJE/IUgj6FNC0p4vqMB/3bwaV6f/gJgkSeTMmTGnRtr36icakeFbgOG+n4mBJhMpH+CMErlhJnO3+7Kq7PoAaA/1EZyHSf5hMBnrU4ZBrFEaGChToDRaiZGPgAiFWs02BlzVXAFLQou3FD+UauH+zbW1kRXCd+OOYTG/ew4yPNPB8SC3CCHwci5QiESVIs+q/qCApLMBVPVq6/EA8bghNsO7VllIRhUqNaHC/X+K9IePaplS38FV7nNd8twLayj0Fv7JSNqD8BwgVWM+p5geadxX9T05fQ5ijqfCP3qablNY1hJWDQMnxbvhExxjSO0BPvaafYOHE/HiokdsCDjLiiBCa4q48O/tiLMgaR0kjpFmD8xcmZj5+fPKTCXKd6jssI9pTEtoon9dQhCo0S/kF174ke7r6vj/9lKr2rTdVGlhNoqhhxNet3AeXppMk7PZ2JxpiYFQIy3CTuCs6Cce4c3Gdn1Ws/iSZi/9PpMhP/hvUxYDO6SMN5AmI7S0=",
                        "active": false,
                        "pending": false,
                        "deleted": 1,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2016-09-01 11:51:43",
                        "stockManagement": 0,
                        "externalData": {
                            "config": null
                        },
                        "displayChannel": "DPD",
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    },
                    "1458": {
                        "id": 1458,
                        "externalId": "1458",
                        "application": "OrderHub",
                        "channel": "interlink-ca",
                        "organisationUnitId": 10949,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "Interlink",
                        "credentials": "xt5MOjdt8njG8ACdxh7Bj0wTCGxMqU0wJXIoG8bM/JuUSSpCM7P+/P7OhAjROM1jnbeSBpT9UDmfgc23CaR2kW/ebcVqrRMwWsxDoC0yzR/adLgTn+TnV7JqGWYV2Te06IV9otvmWG30mOSvrawVTXM956dun/Al/hUAM2E8CJFFnG9nG11DKXfa7CB9X9PeCGGHq+YRuK/n7xI+s7WblT+BU1YSIyhGiSvzKCYIrNtNwDjq7m8RqDsCtYNGAUAufF2pACZKU5L/YF7ClH+5pzwAFalqepI6GjrnMkO5gIMHA1qpoiYBDlSdctRGIeteCz52n4vvlwHAhWQKX/URUiRm8JYUdwCKcRvKw7SuYm4DN4nEnjA8oVNOA0zvMMtapgvuHQDX10NJ3Zwahh8BLQo2XvjFfi8uHkJRYP4OqbCULWard/8jBosdZOPtXJFXF0ZGXuxQEm/vjNOfz2wOlhXAK8ppZsm3YV3xDv8cIglkWExxs9z20i0IBQYjON6xmJLymqwqBmWFo9AK8KPZ15pmOoWHOgAcfpKduqUbNoTNEfnLkDz+eYnpXvWRj4jy5myjKUoi/QaLBBBK0G+bH+61cAgsW8bwaI4Wl1+Hqc8OlWc7a+mrmPpSNj7291kr3zO8oM+C1SASiXjo0oqTfiGiC9jeMSsLRiTz70gLn83dqaLkwbtvFsI9z1JPVA27",
                        "active": false,
                        "pending": false,
                        "deleted": 1,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2016-09-01 12:01:39",
                        "stockManagement": 0,
                        "externalData": {
                            "config": null
                        },
                        "displayChannel": "Interlink",
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    },
                    "1517": {
                        "id": 1517,
                        "externalId": "1517",
                        "application": "OrderHub",
                        "channel": "myhermes-ca",
                        "organisationUnitId": 10949,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "MyHermes",
                        "credentials": "h1S6HNiK87jLSSQ9mQdgnFR3kwFgymBbpYfKOKTOqA6W2GDiHqs4GRc8HFPlDPA4quy31sQY+v7aVieuGjrewV0mV7rExhiVhpnPE2e6YOr1OVk2FS3VVfVSKKhcMy4RVBppxlE1hPgW+Mwe8WHtW10AFemp62BcTQNXsSIzfMwNJVjYpm3yZklFUMWUiUMqJAsyi7QkZUKVOY/z36k0FVgYPMjeq+WdaUm4T8jvmVXJtLJheQjpiYD8C8vFutCMC5JwCEAOJp0EPUiniz+FhkOI7b3s1U0wUt8R/aI4VD+R4JnHhohsCJHfupcz9xMQbc+3FeociXqZJJ8JLHZSRHE1g9FcBCzHHAMaFT8z3Tc=",
                        "active": false,
                        "pending": false,
                        "deleted": 1,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2016-09-19 14:43:13",
                        "stockManagement": 0,
                        "externalData": {
                            "config": null
                        },
                        "displayChannel": "MyHermes",
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    },
                    "1518": {
                        "id": 1518,
                        "externalId": "1518",
                        "application": "OrderHub",
                        "channel": "myhermes-ca",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "MyHermes",
                        "credentials": "KhhFj5g00yulMijSIH+AIhJDZOKhkCOvK8bmDmK5ZiL08EdLU1JvIkoZdb+/nLDwuv78t6Mkh7zjHnj2QyrDzVBYmYp1gIP2otSCu84PvwFEOgRIEGKXIp18kwYHMBkhE0HryaoBVwYnqORH5/vhVz2rmUl3q33+6F9oeKIEGziK5vqf8TjDXJklCGCahkQe+zjY1cPzQc43pLaTI8meQ6i5Fc2NtMglKrStfE3sysmOH8Qw0aNHzDs0R6egbZvvxbvcYDl3bqk6qpllOE6dqUTYu5OSkYXN5ckY2BzyuyjgpF5Qbt0ytCFp5WhngpdcsAzPBSJsbxLi45+KvUcnBCrtlxCbS+0kzxyj470rTR4=",
                        "active": false,
                        "pending": false,
                        "deleted": 1,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2016-09-19 14:48:47",
                        "stockManagement": 0,
                        "externalData": {
                            "config": null
                        },
                        "displayChannel": "MyHermes",
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    },
                    "1519": {
                        "id": 1519,
                        "externalId": "1519",
                        "application": "OrderHub",
                        "channel": "myhermes-ca",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "MyHermes",
                        "credentials": "9ss6kvrwHKcLDKIZomeQeosQVceVYY83oaK2gEN9EWvxyZBgc36LXjeFXjzCqNdZgPJova1NC8lsOmxLEQ6rQkqJQ2ORcwP61RON3qtTVmOtbRodGWI8F6Tif5l4JVwN3V6U0eYv0HeJIAZS5P+y+qiEWNteN3oMI5s6O2Z5ROpFJm4Wmtr+mWhJstqHXxzfVowEr0jgzVV9ovv/I3ovHrm2oR18pJpQ8F9hmbKlWS8Mx/tsuprfKDXHB6yY0TtY1A9rVP/yVR6idBExH0WovwBSiWH/w55ZoqTdrNMvlnIIdQ0VBFezjqlg26DQQsvfnbWToT771KD0iskq+2HTY2vwjQndkoFFQXO0aX0Gr9E=",
                        "active": false,
                        "pending": false,
                        "deleted": 1,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2016-09-19 14:49:22",
                        "stockManagement": 0,
                        "externalData": {
                            "config": null
                        },
                        "displayChannel": "MyHermes",
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    },
                    "1520": {
                        "id": 1520,
                        "externalId": "1520",
                        "application": "OrderHub",
                        "channel": "myhermes-ca",
                        "organisationUnitId": 10949,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "MyHermes",
                        "credentials": "CktJet0qBgjEGaDOHPUDWB6KCpZAtwnby4eUv3mGBDbA/xa91TRzfWZp1W4R16wOwB/A5EXvYZMpxQZg4XtdRDnfapKH+QpL6zD3ppFnXlwU51dBEq/X3ulpR2VUSPoxzaaKsFW7BsopLwlNMkBg6XKPiy/VBawcdGocWFgZUEuptaBhywgJaX34BV5ozh3aECMrB7P7zHfG2awMDizXerCg2zjeiSr4oTiL1ohbMMMYoA+dr5JIWrCpk+KIUSymEkjgeHS1eOSSr/XqoaZ8RrB45XVYFzIlOXEsGydlGA3VTVhCNE6E6AsLmO0pWopCSx0aQDc7oUk04KDDFaGSg/i3aFMIxxL2s1RzO+ucQ0s=",
                        "active": false,
                        "pending": false,
                        "deleted": 1,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2016-09-19 14:53:24",
                        "stockManagement": 0,
                        "externalData": {
                            "config": null
                        },
                        "displayChannel": "MyHermes",
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    },
                    "1822": {
                        "id": 1822,
                        "externalId": "1822",
                        "application": "OrderHub",
                        "channel": "parcelforce-ca",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "Parcelforce",
                        "credentials": "S4gu60+S+paiPJMW7SRwvl1pT6QI2trrbjLFXSU0W52/RtzGLT10+TbGnh65r1EL8/imaGzb67YQgbU/zDCE3v763VAP7gKfrq6ifPlHzluRaXQteGpmKzQQWPp39q6XgTzzAANLI2otAzTrQjZXYY9fUCaYdGyR2QmxOdhfZlbJQBq7cmvOHk08fPy+3DYc9sIGnOXLLpdS1rJ2apJWY03oS6d9DLwXRvfKPrwHW8mofDFl+WK4gZyRUcrlLTp2v2HrzDw9TPkqs7chL+COpbscgph4soytOYCrl/Tq2gAqjVjoC4xaUCzrbZ1RY8U/GpxFcwvJW0Gi6ZgU+4UEYLFeGa5He61pExi+bmwp2Wbase4DJjfipO4anqqwySM4iC/xjKJMg7mD8CLWoLHqzsYw4ZvrM7tg4pQo8tBVDlidp7S+DVg6nDMgogppJ4XbOLW+/62n18TvD1DJNSGLDQ==",
                        "active": false,
                        "pending": false,
                        "deleted": 1,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2016-11-16 15:41:22",
                        "stockManagement": 0,
                        "externalData": {
                            "config": "{\"emailNotification\":\"0\",\"sms_day_of_dispatch\":\"0\",\"sms_start_of_delivery\":\"0\",\"sms_pre_delivery\":\"0\"}"
                        },
                        "displayChannel": "Parcelforce",
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    },
                    "1823": {
                        "id": 1823,
                        "externalId": "2015291000",
                        "application": "OrderHub",
                        "channel": "royal-mail-nd",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "CG OBA",
                        "credentials": "hCrj92WJJBP/ZwLM2YAsOUQpIJgNNHR1miGevkZ+ojdpUQaP02BEdQnku/fS+aZPgK+iCw4eoJuAD+2qY+FuIL3BC2jlfZCvlJ77R+bke8Mpp3/iUSFCenaWyxSbCpId4AsgilA75jx6vp9iJb/JfJ4E4ptVX+xXKkbasftz6zahmX9ZPGyQ5xF5EuPoT5jIPi+1Nrn7NwczmdUgFXYELPjeVCV3Mu4+Fktfd5u15sL0IN8N221wSo/iXsdEb/JkXxwiyyNaUJpplsdrRTF0tMRaHj5iz8NbxjzL/q7DZh5E8zHHGcCbWoQ5ZdRaUWEA8W6qF3Snxk/Q7KgHdmFYPMl82/MFAuqVgJN8JDVKSGpoxdB6Hew4iC1cibJOJYJbyTS/j8VBiAOex5jLRjwGpiX8cK+7tsWRdhcP27uX8SgZqvvBImyH9kp901/V5HYFiDGVJtd8j8zNpTVngEJ9szBrFVKQSrOvob4ZBCOLASpNP47CrYYmcYXcuO0hy1jGvSL7FFXZaYGUXFcZsZ84SxpgZR5GjwPqF0MctEs85xw=",
                        "active": false,
                        "pending": false,
                        "deleted": 1,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2016-11-16 15:45:10",
                        "stockManagement": 0,
                        "externalData": {
                            "accountType": "both",
                            "formSubmissionDate": "2016-11-16 15:45:11",
                            "domesticServices": "",
                            "internationalServices": ""
                        },
                        "displayChannel": null,
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    },
                    "3086": {
                        "id": 3086,
                        "externalId": null,
                        "application": "OrderHub",
                        "channel": "ekm",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "EKM",
                        "credentials": "gwdBr3TbEXYietikBI+mOpc/BS8iOF8h5kCjQkIjHJQteqPeVH7Kpb2PHH8gA4bUD+nu2YnmfNKW8BNqRfvrwxX0jGIXBEBpk/BXbyvuu0KuYyoguq6K3iTbaM7awC2acUBeK5SpaRSnGYB3zODVtFY/6neMK9b5fQOhyWm2itMphSkEicN/9g6z8/Q3myo/eT7Wj1yf2SaeyA1zrp+MwzrbiVt5/800uYARkIvqqu1dYQdKpKcuHH3a5GA6MLupbPB/CPHldaWGnv2kIdNWiWz/6SVSJYI7jmru2Qnvt/mdHmFHjXXOvNl0b/bZoQaEYm9xwCPC6+14hS4bsFnFqBqoaDnl8/1PmPXoOofQ9WQI6Tuhncu0xVJIONdIN6zhIpCtKK0KylBQ5OBnusHFDUhy3F5WFX+n3K6+WbVWbNWOCqmDOdePsCWM9pTBFvpPJkmHeDeuKfu21by9Gpc1KnnwKdUcmWX+X+8kO2m0mgs9xOrlJ7+WC61TQe93w5/QIIPRmC+CjVhOawZwg16M9U89k26aQMoEWAr5PA/MLIlElw/mVdlNwVYig18fh9hPBlJMcpHh5YTFUosyj1pP7fcDImmodxyoH5GbF2elwB11sfNyALsNFvz7mDBJt9Bec3piOaS3mCGGkwbbuUMSWhbpX6Gd/7hC/ZCp0lvon2AXt/pNAPKZYsPURnShF/D/SbVMF9qDFO0Fd6o8dwsUAPFPIBvDl8MfBrqs5VKa07BqorFZ49QiOuR5jTKDAWpY",
                        "active": false,
                        "pending": false,
                        "deleted": 0,
                        "expiryDate": "2019-05-31 22:02:04",
                        "type": [
                            "sales"
                        ],
                        "cgCreationDate": "2017-06-26 15:12:05",
                        "stockManagement": 0,
                        "externalData": {
                            "ekmUsername": "channelgrabber"
                        },
                        "displayChannel": null,
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": 1560910068,
                            "processed": 392,
                            "total": 392,
                            "lastCompletedDate": "2019-06-19 02:13:15"
                        }
                    },
                    "3169": {
                        "id": 3169,
                        "externalId": null,
                        "application": "OrderHub",
                        "channel": "ebay",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "chosen12010",
                        "credentials": "UA2QeZxpw9ep8cbz5gSaMPOI2S682+fB8XnO7q4128yBxcp2d6tlGt2M63SuwTLOnC+IZv8K4nWetiE4o5APCqm997qiPh1GnrNxcwA5b5uhhDOz2jFcSuJ/F6Yl4QjDQLgPTGwTuoVnXob88cO2d1SLEpHkGjXEtR2XOMi9RNW85FyfQpUtzdO9GmSaExjNiGhPGYeI584qaMJxCbAQ+CPLq8i+9Hyg1L7lwdPONSVsDGruBrDX3JeAXdSEV+MD34HWzyAWqqU2NePseh/zr9ZVLBzXj4tKq7su0yrpbq6knM8mCzqZKom3zxeC6rw7R5RCHDrmP1HkzvS0PpGjWz3kx6/9GJEVIMMWJ3JqglQDiWoBalXT41lbtxFOuI3140msja1qdmavPesf5ZAwHq2ryCCtl7DlRLYb5m2EJyH/mPYS0XP+f2n+DgDNXtQOtclPyR/olO68VClw+AoQHSDnqyh3Zc4WjmaLhpsDUz/5PBwp+dm1NQhTlzZiEXk2RDCScILu2ZPddMOclnrn2a4QXwQGag8AMlq7p4sBMU9MLvO073YdrzcVvxNaXnxAoaIZ1WD+W6OdA4SNjdmlNcB2gR4pNjm88paG6kEd+SGGgTTVddA/fee5kS0OtI1S/ydgGqjLsPYIJE+kTcEFc1uAOkyRVMNTN3T2zwjeleRsLTZ4PkXABDokhmO/r0mo6dxjThId+xA7Sq6Jg7MP+Tu6WveS/UbjQrtC8NicbTgxPzu5Xa9rCcK+/HyC4zwdFaCvC3MQSaGkn9MvsRcVKQklQlOISZaILV10B3/4YM5JsDmDIZr8V2hYefC7JuvVXctaiGDRbsw7Ju58vTi65dNXA0myisoNDR0cai/EvNwHszYzaocCdX5af7NXaeCxX+yitu/J5EPMmDDEiFAND9Tsf1wf/bF83bpalEpKsAaSnbvn5RE+6M8xC+oiW+At8zBLEK8SZb4fzfI5sj96Eu1qmpHTYnAUxvCzcHZmnZAlfmji6t0EfxSe0NFulzHKPPcuoUzgFFofrBhDaDXBteqMFquufFm7+vAp4XsVKgA0yAVdfi2U6niUhhw3G4D5zDbqyoKAkNDS1gnVK42sAAInZQGimQo3xO+I3nNECsbg6eFSWXwHkfgAnAq+neVZjcYm6o+qWv8To/MSw6hWE8yJc94JDu4nGKUFLlv0xE4D31sozgIYDhLggqwTAYyVIzNguxEDBo2t3cCcI1UF/+dgKXrZ3wiV4YWZJJxz9MKcLFm80P/2RITcQN9W3eVDN9wX0XPJsMYNyJp3BTbCdifrOk4hoF87oI+IeM6369uLIq8LN0x7ZHM2+GrZfXh8hSBiQnV9H19JI32/45QXaR0TU1BGVWXENy++mudI8F1ear3PDtUvxIg1kM+qHNaGah6braiv6XkYRLlH6b9YYk0BPQjoCwxyQ4HIzs5XLhr4lRoXA1rxuNeMRwV5tT6gBhsELw4vdVzKDDwhBSPb0ei/cbqc3oj9iYppLI8pwGoCQn/vBqDJaRJHoJcL9ZwE4CXFqgegU3arIGXqSFvxqlXQX6Uu+da371pu5MftsABT9j1yJSrXvfGxQrISF5SIz8gzgSHa0o5rZweOaqeq613Gwvsi8lpdMwCUX/kTGWArxeVZjySm/g28fvUgztpZGkAKcCVnSH2bzvLcvaqP6X3ezUAdfG6y+Lv/dHz3ZUFBTRGU+UN3JYDcL88vCkKrrOrsiXSVlgpo8+chOtnKjxnJFYnIWvLj4+qCBcD1NPw=",
                        "active": false,
                        "pending": false,
                        "deleted": 1,
                        "expiryDate": "2018-12-27 09:19:37",
                        "type": [
                            "sales"
                        ],
                        "cgCreationDate": "2017-07-05 09:19:37",
                        "stockManagement": 0,
                        "externalData": {
                            "importEbayEmails": 1,
                            "globalShippingProgram": 0,
                            "listingLocation": null,
                            "listingCurrency": null,
                            "paypalEmail": null,
                            "listingDuration": null,
                            "listingDispatchTime": null,
                            "listingPaymentMethods": [],
                            "oAuthExpiryDate": null
                        },
                        "displayChannel": null,
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        },
                        "listingsAuthActive": false,
                        "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=MzE2OQ==",
                        "siteId": 3
                    },
                    "3170": {
                        "id": 3170,
                        "externalId": null,
                        "application": "OrderHub",
                        "channel": "ebay",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "eBay",
                        "credentials": "YnFTJPtcN2vdvLi3beaLxVmry6ZRq1g7FaltD6+W0fYrDGon1EY6sPOLqIjyUz7LfDd4GSfFV5X3svlWdNQyFNqWO/0nvF+Hte/I+wfv0C/mKOYW3yQ2cfdiTqAcsTkAFZC7gmnS0f42KDKntDyqNqLYvfoMH60r1f5z7GFTlIeeJw0ewRw+Uw5TmveiAcb9Q6NPSycQNxnK7zeAOUvZZ4bsIVWdEwS6wX7K7oP/zjMlSdPau7+E6BjrqkuyfNIDS8Tn9xKvwmigNn2yu1tp8WbrvXXonuAlBYZcmVX1nXwdvw9sMyRQbV1Zm8HB/tR9DloBjadUybCl/dlWFWovHd6xA0d3vgXLDOVF5LpBPLFlOmaAKp24f4Aw35vR8qMW03A6+s8jJvdSBkepwrvlNTiK3RfAx7np3Z9aSBA6P2BVpUrXuUvVwFI30Ub7jLBmIjIIyTsHOIpiM+XPBrQv3g6sdm2+oPh/5k1F+M/6ZAM5Pyml+lgnqEiGdF54EXT1cZiosVxcThC8Z5cg2XmbdU2ZHqQwhArbzZ59ne1moullk19yGywWK3JVeGhy87CHqyyJGZeo1MB/DAikuW0t1Aozage5nhGfkiBzcsrRT29PVyFMGKMWCqNLJQ56dXkllbwd6HuKsxZwGTwnaqJJRWE8bRpaytOjAs9TyUA8Ojo7/+Y4T7ozK3kbP7RMrPeLFFM4rBCCJrsjaDHM3IQOlAQr9AbpOwrb7faBtC22xdXLW3l+WDo+EpulNQ2gNdiyMO9pBinfARuW9UblAJRosEUsw/tgFM9rz55YwVFQpPaMFfJe2EVVWiXsbNlIowvvDARBu2CDm9Ti9my18LHYkLq66NnKiqSwiK1r9fT8jw3nb+UtdTvLXgRIeYCkGkxLcUUGiIdtcbPLdC6U89kmNjcnoTyl9gJQ1q1WzzVGI8FWIy/YLJBGTTRy6728mFnlWrPE6JyCDAidb4V4RE5BQNLFJIY/bICprRoLNUHrIbhjiujhcU/P12NpxtY00r+FdAJmxMO1LnPl2QnNsG7pfEu093Mof25j/NkT973TwdrO/yOsd8sR9KxhzmzKHGl5l3Z2QDyMO2Kc4/mxwUfm6J1Ns/Z3K9eWjLDntck3302oC1Hcm7sVTx8xJ35sTX8VzvBBdspWsavyDs1fCfvwSKhHK4R1zpTTh4a2ZEJV+M6BroxsELe93/3mwTKHhVKK3U+xsLx7LevcRdDpIo6rcP2wylQeyRXsw+d+tw6bb7RTHL3D7Mt5l3dWDpV3KyYGzEQZao+2lm224GvtZd15Ey1FCOBVi/ks0+VYK5bOaz/bPLNDVqCW1deOujg6V0kyHm6Iy0LROngS38G2ZooN8gEaXbUdE2muq7ORC4yXgs4diVPuQo63OubPHMLctFNU/LdgSjXGFyGbzc/TBMy8nxMsijPtVbBCt3A9oUFeIubRLlP3OkXmjPrnNiLQT3NUABFOrDiIH+6DA3fNu3+8o1JDqWcOKvQhRwsRXskuJ5WSpEW39vg8M8dO/F3V6uYe2ET9t2bQhis1CuZmEFo4EMghZZE4+6pco3v+wCTi4plbk/Hf0f9MNUHA8MiGMkCW+ZsNZX5mV24UGbGHcPG+D5LyNPIeUYJTto+yomDArATe+xY1m/PNPgftBqEngESjbv06xXwL4NI+74nch7KDFuWyLLGmZxsisd6u52jybidgkjJzUFrq4fPRvDt8P+XLBjloFlBovlaOjeQGEqJ+2nrBvGBFyvYGrUxiOmXab/6B1o514kFuqz3hQeu4UZjy8Wv/Or9vv2KobqNG/SQ+9Q==",
                        "active": false,
                        "pending": false,
                        "deleted": 0,
                        "expiryDate": "2019-06-10 13:30:29",
                        "type": [
                            "sales"
                        ],
                        "cgCreationDate": "2017-07-05 09:22:16",
                        "stockManagement": 0,
                        "externalData": {
                            "importEbayEmails": 1,
                            "globalShippingProgram": 0,
                            "listingLocation": "Manchester",
                            "listingCurrency": null,
                            "paypalEmail": "accounts@channelgrabber.com",
                            "listingDuration": "GTC",
                            "listingDispatchTime": 1,
                            "listingPaymentMethods": [
                                "PayPal"
                            ],
                            "oAuthExpiryDate": "2020-08-16 01:02:32"
                        },
                        "displayChannel": null,
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": 1560132005,
                            "processed": 0,
                            "total": 0,
                            "lastCompletedDate": "2019-06-05 02:00:15"
                        },
                        "listingsAuthActive": false,
                        "authTokenInitialisationUrl": "https://auth.ebay.com/oauth2/authorize?client_id=ChannelG-9b1e-4478-a742-146c81a2b5a9&redirect_uri=ChannelGrabber_-ChannelG-9b1e-4-wqhbx&response_type=code&scope=https://api.ebay.com/oauth/api_scope%20https://api.ebay.com/oauth/api_scope/sell.marketing.readonly%20https://api.ebay.com/oauth/api_scope/sell.marketing%20https://api.ebay.com/oauth/api_scope/sell.inventory.readonly%20https://api.ebay.com/oauth/api_scope/sell.inventory%20https://api.ebay.com/oauth/api_scope/sell.account.readonly%20https://api.ebay.com/oauth/api_scope/sell.account%20https://api.ebay.com/oauth/api_scope/sell.fulfillment.readonly%20https://api.ebay.com/oauth/api_scope/sell.fulfillment%20https://api.ebay.com/oauth/api_scope/sell.analytics.readonly&state=MzE3MA==",
                        "siteId": 3
                    },
                    "3243": {
                        "id": 3243,
                        "externalId": null,
                        "application": "OrderHub",
                        "channel": "amazon",
                        "organisationUnitId": 10949,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "Amazon EU",
                        "credentials": "7KcoJhcMl9cT5rTLVAL58CRe2hSr87TkfK9zdFb2urppxk7EPnDoisnizeXmEKMFeYlaBwVoYJG/J8rIB+MwSKJkisYeQ3Az/egy0uwOs3UE7Z/UunXuA5xMRIZLI0GkzHYH0DW/67U6/zrSNdNuJ+QXgQLiKIVp93xExHup3yGD0YLHvxNSw58/RoWrRLpL+TXpT6X9VHfigokGHrIu4uwRnyByUyQHaAyeUGBxItx/pbeldHE1T3LJoY8JiGkoQwIUYCiE4jqoLHCwXqzZ9pWQG5LFNiSaGkA7WmOmTsWZbymL7Mg/YA7DzyjuFXS1mfdDFCRN512M1fshjF+X4psCnutqb+k8UgBvEHGedBJtQrfecrFRmkovlWD0Po62JCzpJAGCKIQ6IZqioKZQLEhD4J5AMcmx+CiVHku52/5WvByALEWvk20Mu4nthBHMnrc8whDd1HNzL+Idj6r6PaebVX22E1mb8w7pUUcu20lA9LNwcBoT4VDHHXtO5nPwD4vsPAUykf5es2vuEHZfhXDN4kdJ2s0ZS85dqxK53Qc8IsI19Bifb0Z4ofOFhFApFJ+8Bn6/Dcm8KahJzH76F2cj2bSevXflDLtlbSzQjILyYP9UifJncZLriu6mHKv+b4oshBCzIl03MdWXgoLUrHhgOiGB1jctzTp92TYX0iQwomQDHudS56ZaGGDBMlJtED3F6ojBhNBZQy+vIeBTcbIBgs60H7PgAjbPACtt/nN4zlw/eOFQfUEyWW3g9lqGPaIGwZJFUZeFC1F+DmfyReyoOqOaHhe9HRYWxFVx/QDjTZReHTHtSFIsAVX7HH8lyH8XnJqVKqdIztJWmikcoyj4Djj/LtcOFlktZFRHtZZFZW9i8/gKWj5LuA0QhlwgteoF0cY8KNMqYhpdghRggN3mU66tiXl0hSHLbLsPSORRdJgi4esMT7jXzvFVXhllCqycx0EkrIbnz2HTndq9b6lAdDTxxLIEPcdObHnOAPLPWxlmrxzxxC6922DqkqlK9bmkKtrTHDuN1DrGP2p+Lf3mdrTGoskYwVJ2x89mR0QnnQtQM3jjXst/ujwZfRKtEOMNx1Cwq3uqOSsCTt434A==",
                        "active": true,
                        "pending": false,
                        "deleted": 0,
                        "expiryDate": null,
                        "type": [
                            "sales",
                            "shipping"
                        ],
                        "cgCreationDate": "2017-07-11 10:47:32",
                        "stockManagement": 0,
                        "externalData": {
                            "fbaOrderImport": 0,
                            "hash": "3e474f1d119d973829736ed9f7e18a699ff607bf",
                            "originalEmailAddress": "",
                            "fulfillmentLatency": 2,
                            "mcfEnabled": 1,
                            "messagingSetUp": 0,
                            "includeFbaStock": 0,
                            "stockFromFbaLocationId": 2796,
                            "regionCode": null,
                            "marketplaceIds": "A13V1IB3VIYZZH,A1F83G8C2ARO7P,A1PA6795UKMFR9,A1RKKUPIHCS9HS,A1ZFFQZ3HTUKT9,A38D8NSA03LJTC,A62U237T8HV6N,AFQLKURYRPEL8,APJ6JRA9NG5V4,AZMDEXL2RVFNN"
                        },
                        "displayChannel": null,
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": 1563850808,
                            "processed": 49,
                            "total": 45,
                            "lastCompletedDate": "2019-07-23 03:36:47"
                        }
                    },
                    "3250": {
                        "id": 3250,
                        "externalId": "2015291000",
                        "application": "OrderHub",
                        "channel": "royal-mail-nd",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "Royal Mail - NetDespatch",
                        "credentials": "TPAJ661Zv8weflgLAfGnxOUuidrIqYz7h2VF+DXL62F97ORyRp43wkY66Xf6AidA8MPWZNmo3QiBHLP5T7/mOZ7v69O1wf07NGm/1G9TvAH8RmJXxSmk069W9MTANKOgEkaKQYpSEyRG23qfYHx5bHgg9gM9+ljgEbbfpwVJSIMo0Ve18dFSGz28O5y74k7tcmbHyFe5NWjR2imIEkvQ75Ph4Dw6Xj2yY3d5W6sc4EjiRAJ7PH/01xkQFGuVkbFIARVjk8qeMnY9qOjuqrWoRUrJEpldvcuTj6VSwjtsImEDc7S8vcBuqtuLHQACUqi0em4OSOsEUa6Uty7rodNGhArJLkhmHX5KMX+tjc+tqunrHgTk1PW4OP0gqJx3PqFKnS/DWlQ1DzNe4/OhBzwQF2+zi1ovdVJCtj5Bt+L1fpYGad82rwHa2j6mTTnQXPNGaa3uBpXKDALBz2/s/XZNIXElriH1/h+UCMupDfDiCiDJ2SeHU8J9HnraiYswLVmlUqL468lqoL/9ALeqXeLzIW0LM5lFz6df8cpZtTCV0Ew=",
                        "active": false,
                        "pending": false,
                        "deleted": 0,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2017-07-11 14:38:55",
                        "stockManagement": 0,
                        "externalData": {
                            "accountType": "both",
                            "formSubmissionDate": "2017-07-11 14:38:57",
                            "domesticServices": "",
                            "internationalServices": ""
                        },
                        "displayChannel": null,
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    },
                    "3252": {
                        "id": 3252,
                        "externalId": "3252",
                        "application": "OrderHub",
                        "channel": "myhermes-ca",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "MyHermes",
                        "credentials": "7f0kv+R/TAgt0FTTQa/jAgjjqKeSJJEqscqyXH7V3jN1DE4phocq1MLfFFYGJ0a7AqSagcRmtARNBXvSRlw3nzboLdHgfTKdaOybiEmDmID2zI1cmpNdhi3h1wPelhCWOAkoPGPSCXyndPc0AzVDWHzRte2v76B5WJM7+QuVKgxxELxEMjub5BlN/WbQhjho/rCSTfQPW5Dahflawhb8eRPGKgFq0IdymRAikIXylt0ofznpXkIgxdiqvg9duxViHxmPQ41643IDrsosKt41Bm66fYg4e2WlU00l9ryf7upXbOlhKTFpvHDEDBy/GpDIMp2uHWf10fz1QzOjxdK5YIGwJF2mO9br6rFoN/Y4AVc=",
                        "active": true,
                        "pending": false,
                        "deleted": 0,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2017-07-11 14:43:26",
                        "stockManagement": 0,
                        "externalData": {
                            "config": null
                        },
                        "displayChannel": "MyHermes",
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    },
                    "3336": {
                        "id": 3336,
                        "externalId": "3336",
                        "application": "OrderHub",
                        "channel": "dpd-ca",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "DPD",
                        "credentials": "gQR0bS/8kMl1ZdPqxuByLRgIzhL8ifWeqzS9R90xxZP/0lQR5ZhTPJHxLRD7FfS8PLepaPkA34Oix82txMxXWBzj2oWxO58d6+NV8s7QjYwo2Nt4TcljqCBUa2q9ci2bXAAEaFr1Rmnn/Q/DfLyS0NWbPbDjknBP0+//MK0BHZaHU4sQlMTe621Bor8up9S4jQZpUJpc7uksyCJwxG8LlhzNLlOIB7bov+KWx4zfUtKs93uGnGlfoUXylNVFCCwqJBJkyB++HMZIY9HPslnQ4doB8U8zwTV0zcu3hUdCWahbeEPSR8/zIOQn9GyOftzEWqa/3qB6VwLkg1DbtVU8DyCIbhcrzLaz9sOkl9XnMid+ZT5Gp3+w0auL7svxqiKSjmDvG81uifFbJxZL23Xk8EBpl9Sfy2/kTwpVlOlB4sy0Mm2zN1HeiJXb54tPBt7plNPYFWtxF83Ij53uL+cDpPzot8KqlK4DPQ92Yr6xRqoAqY1Vsw5okylz2k48Rw8q7sipDQYuza+A5v8LMjGVQzpl/gl8rh/WtcNxvyL+D4vSWafxx7GfIglUfschK9EZgxp1pC26UWBt7B41zQQIaSqKRmiCKkN8ZxrbQ5TyHUEY8LlZwJBwGxEye2qW6mL2",
                        "active": true,
                        "pending": false,
                        "deleted": 0,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2017-07-21 14:20:27",
                        "stockManagement": 0,
                        "externalData": {
                            "config": null
                        },
                        "displayChannel": "DPD",
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    },
                    "3337": {
                        "id": 3337,
                        "externalId": "3337",
                        "application": "OrderHub",
                        "channel": "interlink-ca",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "DPD Local",
                        "credentials": "VsgnLNrzpX0lbxDf762CWR/zHY4J57fFZIPPMuHBWSs7q1woeJfGIhJNV3Q8aJufgNlA6g9ipRofQjMMT1GcGEcus5TQYzLrMag3PekIzx/LeW9HxYeJlS6+mVJ6pNki09+ePPLJfUhkZ0DgTghmh0Vk2/0Qst1ur9IvRmdjNoqDtCF9avYJlq3q2nxxf8erS1x6QwMe6h5BM4vOwJc44bt92/ioK6q+KaUJYrbEKDTv+X9skJuACyDVkrdC2o3KquD3ie2hKWfQ1BeXhlfId1WmO0KXFKZpn0bVAIsYPadjlubUmIxBw0IZ2vLMkkVQeJnmIIdmQ1ZDpD7YvZWNN/wYzHavIBa6+0UEeCfr1AlSw0bhg/F/ZWn4j77njvalqMbDQjFaiTALO2NofS3htvF4RQZVwEYXEKwtYZyaiRKotIY1a2tYAWOQmVJOluTPZfwD3WTlvB26xfE6ENiK7sgLiE6/dbrN4xTjAsTAB6d2Z0Hxm1Oa0xaUIy0/Pyg9oUUCkwzAlyP/dpXbtMUQiLbJxJg9uVMLw44lBf8WXVz5KcJK1/Hw8ZDfPWMz+dIj0uInuaA0kVyxz/xx8IUKSf21a03DibXKRGKDM0WSlhAO0+1gSqPQPLkw+srgAg0sRmzIXBk5rpq8opXd1QLv4ugYvkGs7rh+OU7mcl0rNIHIT+m8Hpn3SDeW66PYhnxqtmX2PAJ7K+jDQaKxmCguEA==",
                        "active": true,
                        "pending": false,
                        "deleted": 0,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2017-07-21 14:42:40",
                        "stockManagement": 0,
                        "externalData": {
                            "config": null
                        },
                        "displayChannel": "DPD Local",
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    },
                    "3747": {
                        "id": 3747,
                        "externalId": null,
                        "application": "OrderHub",
                        "channel": "royal-mail",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "Royal Mail (PPI)",
                        "credentials": "Royal Mail",
                        "active": false,
                        "pending": false,
                        "deleted": 0,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2017-08-24 15:38:05",
                        "stockManagement": 0,
                        "externalData": {
                            "PPINumber": ""
                        },
                        "displayChannel": null,
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    },
                    "11660": {
                        "id": 11660,
                        "externalId": null,
                        "application": "OrderHub",
                        "channel": "shopify",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "Shopify",
                        "credentials": "2nfg+u+z7qgiHaUqL7yEp5wBIVm2mDW9bW8IRa/tbJ/NVwBsRfGfZD3QNc4CHhkjidsA6bUMGlGTIcVhdvsB+yEecd65eRhg82xhJ6Phmwg51zsVENmCtRvuQ2tjJGpibW3M8gGAW4IJ+5eAdJbvG9jT9+OqlLLGVK4FSZ9+iQoHjKsQ6DqoQd892BOl7dFkcKLmSbKEAXQQXkRU0D9sMbecSmACoa0CSfBCGTEqgOE=",
                        "active": false,
                        "pending": false,
                        "deleted": 0,
                        "expiryDate": "2019-06-26 12:25:37",
                        "type": [
                            "sales"
                        ],
                        "cgCreationDate": "2018-01-04 16:40:43",
                        "stockManagement": 0,
                        "externalData": {
                            "shopHost": "dev-shopify-orderhub-io.myshopify.com"
                        },
                        "displayChannel": null,
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": 1529671637,
                            "processed": 1,
                            "total": 1,
                            "lastCompletedDate": "2018-06-23 12:47:32"
                        }
                    },
                    "12354": {
                        "id": 12354,
                        "externalId": "47fwg8cpdt",
                        "application": "OrderHub",
                        "channel": "big-commerce",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "BigCommerce",
                        "credentials": "nYg4OGzKeMmDeVdlbKJRBg3WLTlxIWOK0or+WcNloytYBe91o1iu4DDfhTuCiat72+b+3G8yCA32SCXdB2+mzBxnW2mkSfUq73gHOKhQpNkBiA5dT+XYfHokcdHJErxWps+QtofTX0zFmINbvZOvmAvPpLdUblNMW6DI7IJ2wHoGFlo0UjB4b1PIZJ3HbgDZRD3RQWCIHbXgxpuOAKAJx6dHVtnx6hxqVGMrXB2CApU=",
                        "active": true,
                        "pending": false,
                        "deleted": 0,
                        "expiryDate": "2018-07-30 15:12:37",
                        "type": [
                            "sales"
                        ],
                        "cgCreationDate": "2018-02-19 11:20:51",
                        "stockManagement": 0,
                        "externalData": {
                            "secureUrl": "https://store-47fwg8cpdt.mybigcommerce.com",
                            "weightUnits": "kg",
                            "dimensionUnits": "Centimeters"
                        },
                        "displayChannel": null,
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": 1521127671,
                            "processed": 13,
                            "total": 13,
                            "lastCompletedDate": "2018-03-15 15:28:15"
                        }
                    },
                    "12355": {
                        "id": 12355,
                        "externalId": null,
                        "application": "OrderHub",
                        "channel": "ekm",
                        "organisationUnitId": 10949,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "Acme",
                        "credentials": "Xu6vYvSef2w9DAA3fiZvhLYknLkLPyN9lGehw++yU23TAPfkgFESdOvWztrilx4/twu7etv1q/3xrxpQ2ZqZ6lE9MqsxovvQCRYZRTD2anY08cuzlDNJ5Xm7cds/SPcuA0bLkbkeO2SFgVqrc7cF4fJYfO/FLQOY878LYaTvFJL9xT8jx93gzf8TGDctB1IABpkLG3kaZ/7t1gD5adBukAbhzu9CA46r0YyqU4rDqFTGDS2BVp1z/p31ZFTElA42nRsHJdoJ+Q/ICfjLfD+NlELsRWne1dp4Y0x7FzZ6djfcS/ZvtWoPexv7Xz0VcGdz4Bz9odLqI50TOFJ+GPVOeE/XX9k8Hk9Yx2P/j1R082HZpK/NZlKdCD8ovh/g0oO4dSaZNYpKoZs3uUWogN56GvpQlxUf1CtorODaTTwBPPbSpSAEzzbtMfzJJSdbot6lM/hOyurtuwEAmao+V4jZwX7Pqq5DqrTXVZcHTtSUZQ2BXOP6W0ug07rcnXAXAd9dNcmld5d6ywHFhO1U3osZYrbat5niVRORBdmAu3842KhfuHefTnZ9D34H2YpqcR3wVsEd44oPgSY3EJU9n/lvUj/Aohn0Oz+uoOXlXPCiNBl1wQQ61CZSRsQqCF4tWgDdQsUb0wEJJaUSJ/JcnFBiZyEeTk5TnghbxhXSpSvuDcBNgh2cRUhal0mf/8+zRY8rEymTDzRxXoUd9InS3YJS64dfwJvJ4OOmq0bI1BXlMXs3IX/XAzS/Cyj8oAcw3eIx",
                        "active": false,
                        "pending": false,
                        "deleted": 0,
                        "expiryDate": "2019-05-31 22:01:11",
                        "type": [
                            "sales"
                        ],
                        "cgCreationDate": "2018-02-19 11:37:54",
                        "stockManagement": 0,
                        "externalData": {
                            "ekmUsername": "channelgrabber"
                        },
                        "displayChannel": null,
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": 1560910676,
                            "processed": 392,
                            "total": 392,
                            "lastCompletedDate": "2019-06-19 04:34:37"
                        }
                    },
                    "12628": {
                        "id": 12628,
                        "externalId": null,
                        "application": "OrderHub",
                        "channel": "woo-commerce",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "WooCommerce",
                        "credentials": "n74zEWIEcNi2aN1IiseeY4EBTnZxbT+pIt/V6XmdNHJSxK/0SPaiiSWKFbV0qSJtk88YwMaTf2wIgN+32hsCPIertpodyhhccXY1D5C72z07zEe5R48fxaRtPOcZDdbcwQzKbdz6qMta9o5ZyAlsVqYmezGrQ+tugX0sx4wACg1caskGqzGSjsrXMsSzTeG5/S7B1kT9qhXXE7vGBks03Q3l1RK2lbdd1ilO1WorAQZVtZuCugBuPuINcADQ7RhkqloG7UALR5QuF3oTdofh5ZrqKwx8c0FCQErZKn5El9iWO7NvgaHybiizYrIPDUoAacRxpJXx8Z4BjlSrItmwlIMC1XPr/jzOh9CVU/9i0Vo9BkoOpHGXP0ykzP2fHdw1hRaV3UbnEe7QnR5Oqf1t5wdfCNOuVEG3cNTJqSY87l+6XBtN0918lq1vT8p2A5n56GuKMRsuzrb5afgNAfDmXA==",
                        "active": false,
                        "pending": false,
                        "deleted": 0,
                        "expiryDate": null,
                        "type": [
                            "sales"
                        ],
                        "cgCreationDate": "2018-03-09 14:20:55",
                        "stockManagement": 0,
                        "externalData": {
                            "decimalSeparator": ".",
                            "thousandSeparator": ",",
                            "dimensionUnit": "cm",
                            "weightUnit": "kg",
                            "taxIncluded": 0,
                            "currency": "GBP",
                            "sslEnabled": 0,
                            "sslUsed": 0
                        },
                        "displayChannel": null,
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": 1555459231,
                            "processed": 92,
                            "total": 92,
                            "lastCompletedDate": "2019-04-17 00:02:27"
                        }
                    },
                    "12917": {
                        "id": 12917,
                        "externalId": null,
                        "application": "OrderHub",
                        "channel": "royal-mail-click-drop",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "Royal Mail Click & Drop",
                        "credentials": "FyGQTsTo6FxxEgfZP5u/mg3S1GYllTb1Fy7Rs6Va50hJKMOPelFoKHpygFXmXHysCbIR9GpAjIdCpopxwHuwvFAe5o1azYz+WlSKG8VGPJuDDFPhZ2dFPlW2s8DScHpjFO2TnH2D+7DmauR1W/Ttm2v8FIoWY2Go7+S+GE3fq1wZmf3eESt84Dn8hsx39lzz",
                        "active": false,
                        "pending": false,
                        "deleted": 0,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2018-04-03 13:25:10",
                        "stockManagement": 0,
                        "externalData": [],
                        "displayChannel": null,
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    },
                    "14098": {
                        "id": 14098,
                        "externalId": "14098",
                        "application": "OrderHub",
                        "channel": "dpd-ca",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "DPD",
                        "credentials": "uUXAEpiPi895LUMm4TvfhhQDFNZUt6mMfjkRyqEKtBODeuPnx2f5EmQUhp8AQmYbMbyM6B7amdpHW0alXAG7gt/ufw1Ndhb+iK/q8if8V6pNK49HGba7f1jY5/0d9+QEN0TAeadR0eSZeawW1BweyvpGx1b6sLF21ejgecRKHPtFPniv/Ym6EO26D9OSyyZSIZygyintBBX7r9fnCiCA2BRk/IR49CrdYTz5jeEubd8ARsY7MJXZjE6O6/TePqYKzXio4Q+GnA2i4Tc+dt9WbfaiRHQjpT5daot6wDEWeIDtm2fe4w+f44uuitY9S5zSVdcMFO1Piu7LPk6ohoebtoK4OQUZpQVJDMNlO8vgX4zbgT1GxYzDx1FdVtniKnp6eZbt3rp+2h3WNbN27w3NoMWQa9Lo9SHaz6zHgnhTveTFQ/oW142T1n3wEzE4qKAurT2hOix61b2uUO0wP9R95b2v0ryLxLRjmI2KX2pp08UBa9VAgAh22fI1KhK38LVigCE+0doPUcEnF0FeogihMdtC9ZFRP13az2+sfw/p+STwbCpujEdfg4qpDLRAkE0saEUVBBpzSA4ipunEm++PrsqDv2jXlMGeP+ViUD+m/BcW5von+M/d3q9JsjT7hzak",
                        "active": false,
                        "pending": false,
                        "deleted": 0,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2018-08-23 13:00:52",
                        "stockManagement": 0,
                        "externalData": {
                            "config": null
                        },
                        "displayChannel": "DPD",
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    },
                    "15504": {
                        "id": 15504,
                        "externalId": "15504",
                        "application": "OrderHub",
                        "channel": "royal-mail-intersoft-ca",
                        "organisationUnitId": 10558,
                        "rootOrganisationUnitId": 10558,
                        "displayName": "Royal Mail OBA",
                        "credentials": "cbwMXR2z3oeu91Jcqi8PbLZwsbe6mRSmKotJNhSP740WFyH/xN+I8AzruE/RgDS0urTeMdRLX/EaaeVaVszk/SnJVyih4VJyOmskqrzzGVrA5iaPGM1nLcY4rwzZZd9DFNLYqEoSbqgJ141orsmCLVL2ApOLY7SpfePZywCmFnKlTJ3FDsSLix4kX0wjuIWi3QJYAMFahOrb6RXR24BVijZ/x9mniiKUBkzJZccDuvGcpRLcNWgnl//d/Hspnum7fmCjhcVZpeKcTjyCPlNKyrbwqtYYCyiADvh4zzQt1rFPR4DlSE6z87M85yPTiAr/1UWTohXOrE/vOPSZmxw5cOg6Y6weDvi2GDtu4CZvR2+Q3k89NyyAIT/VLGMugWmJKVf+aibk6dpd5E4URa23CA==",
                        "active": true,
                        "pending": false,
                        "deleted": 0,
                        "expiryDate": null,
                        "type": [
                            "shipping"
                        ],
                        "cgCreationDate": "2019-04-18 09:29:14",
                        "stockManagement": 0,
                        "externalData": {
                            "config": null
                        },
                        "displayChannel": "Royal Mail OBA (In)",
                        "orderNotificationUrl": "",
                        "stockNotificationUrl": "",
                        "stockMaximumEnabled": false,
                        "stockFixedEnabled": false,
                        "autoImportListings": false,
                        "listingDownload": {
                            "id": null,
                            "processed": null,
                            "total": null,
                            "lastCompletedDate": null
                        }
                    }
                },
                "stockModeDefault": "all",
                "stockLevelDefault": null,
                "lowStockThresholdDefault": {
                    "toggle": true,
                    "value": 5
                },
                "taxRates": {
                    "GB": {
                        "GB1": {
                            "name": "Standard",
                            "rate": 20
                        },
                        "GB2": {
                            "name": "Reduced",
                            "rate": 5
                        },
                        "GB3": {
                            "name": "Zero",
                            "rate": 0,
                            "selected": true
                        }
                    }
                },
                "variationCount": 3,
                "variationIds": [
                    "11400132",
                    "11400134",
                    "11409247"
                ]
            },
            "attributeNames": [
                "Colour"
            ],
            "variationImages": {
                "EXRED": {
                    "imageId": 13812565
                },
                "EXBLU": {
                    "imageId": 13812565
                },
                "EXWHI": {
                    "imageId": 13812565
                }
            },
            "attributeNameMap": {},
            "renderImagePicker": true,
            "shouldRenderStaticImagesFromVariationValues": false,
            "containerCssClasses": "",
            "tableCssClasses": "",
            "renderStaticImageFromFormValues": false
        };



//        let productCopy = Object.assign({}, props.product);
//        let { id, sku, name, imageIds, images } = productCopy;
//        let newProduct = { id, sku, name, imageIds, images };
//        props.product = newProduct;


        console.log('newProps: ', props);


        return (<span>
            <span className="heading-large heading-table">Product Identifiers</span>
            <ProductIdentifiers
                {...props}
            />
        </span>);



//
//        return (<span>
//            <span className="heading-large heading-table">Product Identifiers</span>
//            <ProductIdentifiers
//                variationsDataForProduct={this.props.variationsDataForProduct}
//                product={this.props.product}
//                attributeNames={this.props.product.attributeNames}
//                variationImages={this.props.variationImages}
//            />
//        </span>);
    };

    renderDimensions = () => {
        // todo remove these hacks
        dimensionsProps.change = this.props.change;
        //cutting it down to reduce lag
        dimensionsProps.variationsDataForProduct = [dimensionsProps.variationsDataForProduct[0]]

//        return (<span>
//            <span className="heading-large heading-table">Dimensions</span>
//            <Dimensions
//                {...dimensionsProps}
//            />
//        </span>);

        return <DimensionsSection {...dimensionsProps} />




//        return (<span>
//            <span className="heading-large heading-table">Dimensions</span>
//            <Dimensions
//                variationsDataForProduct={this.props.variationsDataForProduct}
//                product={this.props.product}
//                attributeNames={this.props.product.attributeNames}
//                change={this.props.change}
//                initialDimensions={this.props.initialDimensions}
//                accounts={this.getSelectedAccountsData()}
//                massUnit={this.props.massUnit}
//                lengthUnit={this.props.lengthUnit}
//                variationImages={this.props.variationImages}
//            />
//        </span>);
    };

    renderProductPrices = () => {
        return (<span>
            <span className="heading-large heading-table">Price</span>
            <ProductPrice
                variationsDataForProduct={this.props.variationsDataForProduct}
                product={this.props.product}
                attributeNames={this.props.product.attributeNames}
                change={this.props.change}
                accounts={this.getSelectedAccountsData()}
                initialPrices={this.props.initialProductPrices}
                currency={this.props.defaultCurrency}
                variationImages={this.props.variationImages}
            />
        </span>);
    };

    getSelectedAccountsData = () => {
        let accounts = [];
        this.props.accounts.map(function(accountId) {
            accounts.push(this.props.accountsData[accountId]);
        }.bind(this));
        return accounts;
    };
//
    renderSubmissionTable = () => {
//
        return (<span>
            <SubmissionTable
                accounts={this.formatAccountDataForSubmissionTable()}
                categoryTemplates={this.props.categoryTemplates.categories}
                statuses={this.props.submissionStatuses}
            />
        </span>);
    };

    formatAccountDataForSubmissionTable = () => {
        var accounts = {};
        this.props.accounts.forEach(accountId => {
            accounts[accountId] = this.props.accountsData[accountId]
        });
        return accounts;
    };

    isSubmitButtonDisabled = () => {
        return this.props.submissionStatuses.inProgress;
    };

    areAllListingsSuccessful = () => {
        let accounts = this.props.submissionStatuses.accounts;
        if (Object.keys(accounts).length === 0) {
            return false;
        }

        let hasStatusForAccountsAndCategories = false;
        for (let accountId in accounts) {
            let account = accounts[accountId];
            for (let categoryId in account) {
                let category = account[categoryId];
                if (category.status !== "completed") {
                    return false;
                }
                hasStatusForAccountsAndCategories = true;
            }
        }

        return hasStatusForAccountsAndCategories;
    };

    areCategoryTemplatesFetching = () => {
        return this.props.categoryTemplates.isFetching;
    };

    validateProductAssignation = (event) => {
        if (this.isPbseRequired() && !this.areAllVariationsAssigned()) {
            event.preventDefault();
            this.addVariationErrorOnProductSearch();
            return;
        }

        if (this.props.productSearch.error) {
            this.props.clearErrorFromProductSearch();
        }
    };

    areAllVariationsAssigned = () => {
        return this.props.variationsDataForProduct.every(variation => {
            return !!(this.props.productSearch.selectedProducts[variation.id]);
        });
    };

    isPbseRequired = () => {
        if (this.props.variationsDataForProduct.length === 1) {
            return false;
        }

        if (!this.props.categoryTemplates.categories) {
            return false;
        }

        return Object.values(this.props.categoryTemplates.categories).some(categoryTemplate => {
            return Object.values(categoryTemplate.accounts).some(category => {
                return category.channel == 'ebay' && category.fieldValues && category.fieldValues.pbse && category.fieldValues.pbse.required;
            });
        });
    };

    addVariationErrorOnProductSearch = () => {
        this.props.addErrorOnProductSearch('You must assign a product to all your variations. This must be done because one of your selected eBay categories requires all the variations of your product to be mapped to existing products.');
    };

    buildSections = () => {
        const productSearchComponent = this.renderProductSearchComponent();

        const sections = [
            new SectionData('Listing Information', this.renderForm()),
            new SectionData('Listing creation status', this.renderSubmissionTable())
        ];

        if (productSearchComponent) {
            sections.unshift(this.buildProductSearchSectionData(productSearchComponent));
        }

        return sections;
    };

    buildProductSearchSectionData = (productSearchComponent) => {
        return new SectionData(
            'Search for your product',
            productSearchComponent,
            this.validateProductAssignation,
            this.isYesButtonDisabledForProductSearch()
        );
    };

    isYesButtonDisabledForProductSearch = () => {
        return this.props.categoryTemplates.isFetching;
    };

    submitForm = () => {
        if (this.isPbseRequired() && !this.areAllVariationsAssigned()) {
            this.addVariationErrorOnProductSearch();
            $('html, body').animate({
                scrollTop: ($("a[name=section0]").offset().top)
            }, 200);
            return;
        }

        this.props.submitForm();
    };

    getYesButtonText = () => {
        if (this.isSubmitButtonDisabled() || this.areCategoryTemplatesFetching()) {
            return 'Submitting...';
        }

        if (this.areAllListingsSuccessful()) {
            return 'All done';
        }

        return 'Submit';
    };

    render() {
        return <SectionedContainer
            sectionClassName={"editor-popup product-create-listing"}
            yesButtonText={this.getYesButtonText()}
            noButtonText="Cancel"
            onYesButtonPressed={this.submitForm}
            onNoButtonPressed={this.props.onCreateListingClose}
            onBackButtonPressed={this.props.onBackButtonPressed.bind(this, this.props.product)}
            yesButtonDisabled={(this.isSubmitButtonDisabled() || this.areAllListingsSuccessful() || this.areCategoryTemplatesFetching())}
            sections={this.buildSections()}
        />;
    }
}

CreateListingPopup = reduxForm({
    form: "createListing",
    enableReinitialize: true,
    // This is required to make the images in the variation table show correctly
    keepDirtyOnReinitialize: true,
    onSubmit: submitForm,
})(CreateListingPopup);

const mapStateToProps = function(state) {
    return {
        initialValues: state.initialValues,
        initialDimensions: state.initialValues.dimensions ? Object.assign(state.initialValues.dimensions) : {},
        initialProductPrices: state.initialValues.prices ? Object.assign(state.initialValues.prices) : {},
        submissionStatuses: JSON.parse(JSON.stringify(state.submissionStatuses)),
        resetSection: resetSection,
        categoryTemplates: state.categoryTemplates,
        productSearch: state.productSearch,
        variationImages: FormSelector(state, 'images')
    };
};

const mapDispatchToProps = function(dispatch, props) {
    return {
        submitForm: function() {
            dispatch(submit("createListing"));
        },
        loadInitialValues: function(searchAccountId) {
            dispatch(
                Actions.loadInitialValues(
                    props.product,
                    props.variationsDataForProduct,
                    props.accounts,
                    props.accountDefaultSettings,
                    props.accountsData,
                    props.categoryTemplates ? props.categoryTemplates.categories : {},
                    searchAccountId
                )
            );
        },
        revertToInitialValues: function () {
            dispatch(Actions.revertToInitialValues());
        },
        fetchCategoryTemplateDependentFieldValues: function() {
            dispatch(Actions.fetchCategoryTemplateDependentFieldValues(props.categories, props.accountDefaultSettings, props.accountsData, dispatch));
        },
        clearSelectedProduct: function(productId) {
            dispatch(Actions.clearSelectedProduct(productId, props.variationsDataForProduct));
        },
        addErrorOnProductSearch: function(errorMessage) {
            dispatch(Actions.addErrorOnProductSearch(errorMessage));
        },
        clearErrorFromProductSearch: function() {
            dispatch(Actions.clearErrorFromProductSearch());
        }
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(CreateListingPopup);
