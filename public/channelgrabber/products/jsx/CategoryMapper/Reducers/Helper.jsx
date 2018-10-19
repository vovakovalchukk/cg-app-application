
    

    export default {
        invalidateSelectedCategoriesForAccount: function (categoryMap, accountId) {
            categoryMap = Object.assign({}, categoryMap);
            categoryMap.selectedCategories = Object.assign({}, categoryMap.selectedCategories);
            categoryMap.selectedCategories[accountId] = [];
            return categoryMap;
        },
        extractSelectedCategoryDataFromCategoryMap: function (categoryMap) {
            var accountCategories = categoryMap.accountCategories;
            var newCategoryMap = {
                selectedCategories: {},
                name: categoryMap.name,
                etag: categoryMap.etag
            };

            accountCategories.map(function (categoriesForAccount) {
                var selectedCategories = [];
                categoriesForAccount.categories.map(function (categoriesByLevel) {
                    selectedCategories = [];
                    categoriesByLevel.map(function (categories, level) {
                        categories.map(function (category) {
                            if (category.selected) {
                                selectedCategories.push(category.value);
                            }
                        });
                    });
                });
                newCategoryMap.selectedCategories[categoriesForAccount.accountId] = selectedCategories;
            });

            return newCategoryMap;
        }
    }

