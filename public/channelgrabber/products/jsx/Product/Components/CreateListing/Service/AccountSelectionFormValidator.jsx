define([
    'react'
], function(
    React
) {
   "use strict";

    var service = {
        findAccountIndexForAccountId: function(accountId, props) {
            var index = 0;
            for (var id in props.accounts) {
               if (id == accountId) {
                   return index;
               }
               index++;
            }
            return null;
        },
        categoryTemplateHasAccountId: function (categoryTemplate, accountId) {
            if (!categoryTemplate.selected) {
              return true;
            }
            return accountId in categoryTemplate.accounts;
        },
        validateAccounts: function(values, props) {
            var accountsError = {},
               accounts = [];

            values && values.accounts && values.accounts.forEach(function(accountId) {
               if (!accountId) {
                   return;
               }
               accounts.push(accountId);

               var accountIndex = service.findAccountIndexForAccountId(accountId, props);
               if (accountIndex === null) {
                   return;
               }

               var invalidCategoryMapsForAccount = [];
               for (var categoryTemplateId in props.categoryTemplateOptions) {
                   var categoryTemplate = props.categoryTemplateOptions[categoryTemplateId];
                   if (!service.categoryTemplateHasAccountId(categoryTemplate, accountId)) {
                       invalidCategoryMapsForAccount.push(categoryTemplate.name);
                   }
               }

               if (invalidCategoryMapsForAccount.length > 0) {
                   accountsError[accountIndex] = "You cannot choose this account because the following category maps don't have " +
                       "any mapped categories for it: " + invalidCategoryMapsForAccount.join(", ");
               }
            });

            if (accounts.length === 0) {
               accountsError._error = "Please select at least one account.";
            }

            return Object.keys(accountsError).length > 0 ? accountsError : null;
        },
        validateCategories: function(values) {
            if (values.categories.length === 0) {
                return "Please select at least one category.";
            }
            return null;
        },
        validateAccountListingSettings: function(props) {
            var errors = {};
            for (var accountId in props.accountSettings) {
                var settings = props.accountSettings[accountId];
                if (settings.error) {
                    var accountIndex = service.findAccountIndexForAccountId(accountId, props);
                    if (accountIndex === null) {
                        return;
                    }
                    errors[accountIndex] = "<span>Test</span>";
                }
            }
            return Object.keys(errors).length > 0 ? errors : null;
        }
    };

    return function (formValues, props) {
        var errors = {},
            accountsErrors = service.validateAccounts(formValues, props),
            categoryErrors = service.validateCategories(formValues);

        if (accountsErrors) {
            errors.accounts = accountsErrors;
        } else {
            accountsErrors = service.validateAccountListingSettings(props);
            if (accountsErrors) {
                errors.accounts = accountsErrors;
            }
        }
        if (categoryErrors) {
            errors.categories = categoryErrors;
        }

        return errors;
    };
});
