export default function() {
    const selectorPrefix = '.js-page-categories';
    if ($(selectorPrefix).length === 0) {
        return;
    }

    $(`.js-edit-category-start`).click(function (e) {
        let categoryId = parseInt($(this).attr('data-category-id'));
        $(`.js-edit-category-id`).val(categoryId);

        let categoryCode = $(this).attr('data-category-code');
        $(`.js-edit-category-code`).val(categoryCode);

        let categoryName = $(this).attr('data-category-name');
        $(`.js-edit-category-name`).val(categoryName);
    });
}