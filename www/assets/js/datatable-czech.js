if (window.jQuery && jQuery.fn && jQuery.fn.dataTable) {
    jQuery.extend(jQuery.fn.dataTableExt.oSort, {
        "czech-asc": function (s1, s2) {
            return s1.localeCompare(s2, 'cs', {sensitivity: 'base'});
        },
        "czech-desc": function (s1, s2) {
            return s2.localeCompare(s1, 'cs', {sensitivity: 'base'});
        }
    });

    jQuery.fn.DataTable.ext.type.search['czech'] = function (data) {
        if (!data) return '';
        return removeDiacritics(data.toString().toLowerCase());
    };

    function removeDiacritics(str) {
        return str
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .replace(/ř/g, 'r').replace(/Ř/g, 'R')
            .replace(/ž/g, 'z').replace(/Ž/g, 'Z')
            .replace(/č/g, 'c').replace(/Č/g, 'C')
            .replace(/ď/g, 'd').replace(/Ď/g, 'D')
            .replace(/ť/g, 't').replace(/Ť/g, 'T')
            .replace(/ň/g, 'n').replace(/Ň/g, 'N')
            .replace(/ě/g, 'e').replace(/Ě/g, 'E')
            .replace(/ů/g, 'u').replace(/Ů/g, 'U')
            .replace(/ú/g, 'u').replace(/Ú/g, 'U')
            .replace(/ý/g, 'y').replace(/Ý/g, 'Y')
            .replace(/í/g, 'i').replace(/Í/g, 'I')
            .replace(/é/g, 'e').replace(/É/g, 'E')
            .replace(/á/g, 'a').replace(/Á/g, 'A');
    }
}