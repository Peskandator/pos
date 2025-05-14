$(document).ready(function() {
    jQuery.extend(jQuery.fn.dataTableExt.oSort, {
        "czech-asc": function (s1, s2) {
            return s1.localeCompare(s2, 'cs', { sensitivity: 'base' });
        },
        "czech-desc": function (s1, s2) {
            return s2.localeCompare(s1, 'cs', { sensitivity: 'base' });
        }
    });

    jQuery.fn.DataTable.ext.type.search['czech'] = function (data) {
        if (!data) return '';
        return removeDiacritics(data.toString().toLowerCase());
    };

    function removeDiacritics(str) {
        return str
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "") // Remove accents
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

    $('.js-table').DataTable(
        {
            language: getLanguage(),
            scrollX: false,
            responsive: false,
            columnDefs: [
                {type: 'czech', targets: "_all"},
            ],
            paging: false
        }
    );

    $('.js-table-products').DataTable(
        {
            language: getLanguage(),
            scrollX: false,
            responsive: false,
            columnDefs: [
                {type: 'num', targets: [0, 2]},
                {type: 'czech', targets: "_all"},
                {orderable: false, targets: -1}
            ],
            paging: true
        }
    );

    $('.js-table-products-filtered').DataTable(
        {
            language: getLanguage(),
            scrollX: false,
            responsive: false,
            columnDefs: [
                {type: 'num', targets: [0, 2, 3, 4]},
                {type: 'czech', targets: "_all"},
            ],
            paging: true
        }
    );

    $('.js-table-dials').DataTable(
        {
            language: getLanguage(),
            scrollX: false,
            responsive: false,
            columnDefs: [
                {type: 'num', targets: [0]},
                {type: 'czech', targets: "_all"},
                {orderable: false, targets: -1}
            ],
            paging: true
        }
    );

    $('.js-table-orders').DataTable(
        {
            language: getLanguage(),
            scrollX: false,
            responsive: false,
            columnDefs: [
                {type: 'num', targets: [0, 1, 4, 5]},
                {type: 'czech', targets: "_all"},
                {orderable: false, targets: [-1, 3]}
            ],
            paging: true
        }
    );

    $('.js-table-orders-filtered').DataTable(
        {
            language: getLanguage(),
            scrollX: false,
            responsive: false,
            columnDefs: [
                {type: 'num', targets: [0, 3, 4, 5, 6]},
                {type: 'czech', targets: "_all"},
                {orderable: false, targets: -1}
            ],
            paging: true
        }
    );

    $('.js-table-payment-items').DataTable(
        {
            language: getLanguage(),
            scrollX: false,
            responsive: false,
            columnDefs: [
                {type: 'num', targets: [2, 3]},
                {type: 'czech', targets: "_all"},
                {orderable: false, targets: 0}
            ],
            paging: false,
            order: []
        }
    );

    $('.js-table-order-item-states').DataTable(
        {
            language: getLanguage(),
            scrollX: false,
            responsive: false,
            columnDefs: [
                {type: 'num', targets: [1, 2, 3]},
                {type: 'czech', targets: "_all"},
            ],
            paging: false,
            order: []
        }
    );

    function getLanguage() {
        return {
            "emptyTable": "Tabulka neobsahuje žádná data",
            "info": "Zobrazuji _START_ až _END_ z celkem _TOTAL_ záznamů",
            "infoEmpty": "Zobrazuji 0 až 0 z 0 záznamů",
            "infoFiltered": "(filtrováno z celkem _MAX_ záznamů)",
            "loadingRecords": "Načítám...",
            "zeroRecords": "Žádné záznamy nebyly nalezeny",
            "paginate": {
                "first": "První",
                "last": "Poslední",
                "next": "Další",
                "previous": "Předchozí"
            },
            "searchBuilder": {
                "add": "Přidat podmínku",
                "clearAll": "Smazat vše",
                "condition": "Podmínka",
                "conditions": {
                    "date": {
                        "after": "po",
                        "before": "před",
                        "between": "mezi",
                        "empty": "prázdné",
                        "equals": "rovno",
                        "not": "není",
                        "notBetween": "není mezi",
                        "notEmpty": "není prázdné"
                    },
                    "number": {
                        "between": "mezi",
                        "empty": "prázdné",
                        "equals": "rovno",
                        "gt": "větší",
                        "gte": "rovno a větší",
                        "lt": "menší",
                        "lte": "rovno a menší",
                        "not": "není",
                        "notBetween": "není mezi",
                        "notEmpty": "není prázdné"
                    },
                    "string": {
                        "contains": "obsahuje",
                        "empty": "prázdné",
                        "endsWith": "končí na",
                        "equals": "rovno",
                        "not": "není",
                        "notEmpty": "není prázdné",
                        "startsWith": "začíná na",
                        "notContains": "Podmínka",
                        "notStarts": "Nezačíná",
                        "notEnds": "Nekončí"
                    },
                    "array": {
                        "equals": "rovno",
                        "empty": "prázdné",
                        "contains": "obsahuje",
                        "not": "není",
                        "notEmpty": "není prázdné",
                        "without": "neobsahuje"
                    }
                },
                "data": "Sloupec",
                "logicAnd": "A",
                "logicOr": "NEBO",
                "title": {
                    "0": "Rozšířený filtr",
                    "_": "Rozšířený filtr (%d)"
                },
                "value": "Hodnota",
                "button": {
                    "0": "Rozšířený filtr",
                    "_": "Rozšířený filtr (%d)"
                },
                "deleteTitle": "Smazat filtrovací pravidlo"
            },
            "autoFill": {
                "cancel": "Zrušit",
                "fill": "Vyplň všechny buňky textem <i>%d<i><\/i><\/i>",
                "fillHorizontal": "Vyplň všechny buňky horizontálně",
                "fillVertical": "Vyplň všechny buňky vertikálně"
            },
            "buttons": {
                "collection": "Kolekce <span class=\"ui-button-icon-primary ui-icon ui-icon-triangle-1-s\"><\/span>",
                "copy": "Kopírovat",
                "copyTitle": "Kopírovat do schránky",
                "csv": "CSV",
                "excel": "Excel",
                "pageLength": {
                    "-1": "Zobrazit všechny řádky",
                    "_": "Zobrazit %d řádků"
                },
                "pdf": "PDF",
                "print": "Tisknout",
                "colvis": "Viditelnost sloupců",
                "colvisRestore": "Resetovat sloupce",
                "copyKeys": "Zmáčkněte ctrl or u2318 + C pro zkopírování dat.  Pro zrušení klikněte na tuto zprávu nebo zmáčkněte esc..",
                "copySuccess": {
                    "1": "Zkopírován 1 řádek do schránky",
                    "_": "Zkopírováno %d řádků do schránky"
                },
                "createState": "Vytvořit Stav",
                "removeAllStates": "Vymazat všechny Stavy",
                "removeState": "Odstranit",
                "renameState": "Odstranit",
                "savedStates": "Uložit Stavy",
                "stateRestore": "Stav %d",
                "updateState": "Aktualizovat"
            },
            "searchPanes": {
                "clearMessage": "Smazat vše",
                "collapse": {
                    "0": "Vyhledávací Panely",
                    "_": "Vyhledávací Panely (%d)"
                },
                "count": "{total}",
                "countFiltered": "{shown} ({total})",
                "emptyPanes": "Žádné Vyhledávací Panely",
                "loadMessage": "Načítám Vyhledávací Panely",
                "title": "Aktivních filtrů - %d",
                "showMessage": "Zobrazit Vše",
                "collapseMessage": "Sbalit Vše"
            },
            "select": {
                "cells": {
                    "1": "Vybrán 1 záznam",
                    "_": "Vybráno %d záznamů"
                },
                "columns": {
                    "1": "Vybrán 1 sloupec",
                    "_": "Vybráno %d sloupců"
                }
            },
            "aria": {
                "sortAscending": "Aktivujte pro seřazení vzestupně",
                "sortDescending": "Aktivujte pro seřazení sestupně"
            },
            "lengthMenu": "Zobrazit _MENU_ výsledků",
            "processing": "Zpracovávání...",
            "search": "Vyhledávání:",
            "datetime": {
                "previous": "Předchozí",
                "next": "Další",
                "hours": "Hodiny",
                "minutes": "Minuty",
                "seconds": "Vteřiny",
                "unknown": "-",
                "amPm": [
                    "Dopoledne",
                    "Odpoledne"
                ],
                "weekdays": [
                    "Po",
                    "Út",
                    "St",
                    "Čt",
                    "Pá",
                    "So",
                    "Ne"
                ],
                "months": [
                    "Leden",
                    "Únor",
                    "Březen",
                    "Duben",
                    "Květen",
                    "Červen",
                    "Červenec",
                    "Srpen",
                    "Září",
                    "Říjen",
                    "Listopad",
                    "Prosinec"
                ]
            },
            "editor": {
                "close": "Zavřít",
                "create": {
                    "button": "Nový",
                    "title": "Nový záznam",
                    "submit": "Vytvořit"
                },
                "edit": {
                    "button": "Změnit",
                    "title": "Změnit záznam",
                    "submit": "Aktualizovat"
                },
                "remove": {
                    "button": "Vymazat",
                    "title": "Smazání",
                    "submit": "Vymazat",
                    "confirm": {
                        "_": "Opravdu chcete smazat tyto %d řádky?",
                        "1": "Opravdu chcete smazat tento 1 řádek?"
                    }
                },
                "multi": {
                    "title": "Mnohočetný výběr",
                    "restore": "Vrátit změny",
                    "noMulti": "Toto pole může být editováno individuálně, ale ne jako soušást skupiny."
                }
            },
            "infoThousands": " ",
            "decimal": ",",
            "thousands": " ",
            "stateRestore": {
                "creationModal": {
                    "button": "Vytvořit",
                    "columns": {
                        "search": "Vyhledávání v buňce",
                        "visible": "Viditelnost buňky"
                    },
                    "name": "Název:",
                    "order": "Řazení",
                    "paging": "Stránkování",
                    "scroller": "Pozice skrolování",
                    "search": "Hledání",
                    "searchBuilder": "SearchBuilder",
                    "select": "Výběr",
                    "title": "Vytvořit nový Stav",
                    "toggleLabel": "Zahrnout"
                },
                "duplicateError": "Stav s tímto názvem ji existuje.",
                "emptyError": "Název nemůže být prázný.",
                "emptyStates": "Žádné uložené stavy",
                "removeConfirm": "Opravdu chcete odstranbit %s?",
                "removeError": "Chyba při odstraňování stavu.",
                "removeJoiner": "a",
                "removeSubmit": "Odstranit",
                "removeTitle": "Odstranit Stav",
                "renameButton": "Vymazat",
                "renameLabel": "Nové jméno pro %s:",
                "renameTitle": "Přejmenování Stavu"
            }
        };
    }
});