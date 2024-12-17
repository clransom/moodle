M.mod_assign = {};

M.mod_assign.init_grading_table = function(Y) {
    Y.use('node', function(Y) {
        const checkboxes = Y.all('td.c0 input');
        let rowelement;
        checkboxes.each(function(node) {
            node.on('change', function(e) {
                rowelement = e.currentTarget.get('parentNode').get('parentNode');
                if (e.currentTarget.get('checked')) {
                    rowelement.removeClass('unselectedrow');
                    rowelement.addClass('selectedrow');
                } else {
                    rowelement.removeClass('selectedrow');
                    rowelement.addClass('unselectedrow');
                }
            });

            rowelement = node.get('parentNode').get('parentNode');
            if (node.get('checked')) {
                rowelement.removeClass('unselectedrow');
                rowelement.addClass('selectedrow');
            } else {
                rowelement.removeClass('selectedrow');
                rowelement.addClass('unselectedrow');
            }
        });

        const selectall = Y.one('th.c0 input');
        if (selectall) {
            selectall.on('change', function(e) {
                Y.all('td.c0 input[type="checkbox"]').each(function(node) {
                    rowelement = node.get('parentNode').get('parentNode');
                    if (e.currentTarget.get('checked')) {
                        node.set('checked', true);
                        rowelement.removeClass('unselectedrow');
                        rowelement.addClass('selectedrow');
                    } else {
                        node.set('checked', false);
                        rowelement.removeClass('selectedrow');
                        rowelement.addClass('unselectedrow');
                    }
                });
            });
        }

        var quickgrade = Y.all('.gradingtable .quickgrade');
        quickgrade.each(function(quick) {
            quick.on('change', function(e) {
                this.get('parentNode').addClass('quickgrademodified');
            });
        });
    });
};

M.mod_assign.init_plugin_summary = function(Y, subtype, type, submissionid) {
    var suffix = subtype + '_' + type + '_' + submissionid;
    var classname = 'contract_' + suffix;
    var contract = Y.one('.' + classname);
    if (contract) {
        contract.on('click', function(e) {
            e.preventDefault();
            var link = e.currentTarget || e.target;
            var linkclasses = link.getAttribute('class').split(' ');
            var thissuffix = '';
            for (var i = 0; i < linkclasses.length; i++) {
                classname = linkclasses[i];
                if (classname.indexOf('contract_') == 0) {
                    thissuffix = classname.substr(9);
                }
            }
            var fullclassname = 'full_' + thissuffix;
            var full = Y.one('.' + fullclassname);
            if (full) {
                full.hide(false);
            }
            var summaryclassname = 'summary_' + thissuffix;
            var summary = Y.one('.' + summaryclassname);
            if (summary) {
                summary.show(false);
                summary.one('a.expand_' + thissuffix).focus();
            }
        });
    }
    classname = 'expand_' + suffix;
    var expand = Y.one('.' + classname);

    var full = Y.one('.full_' + suffix);
    if (full) {
        full.hide(false);
        full.toggleClass('hidefull');
    }
    if (expand) {
        expand.on('click', function(e) {
            e.preventDefault();
            var link = e.currentTarget || e.target;
            var linkclasses = link.getAttribute('class').split(' ');
            var thissuffix = '';
            for (var i = 0; i < linkclasses.length; i++) {
                classname = linkclasses[i];
                if (classname.indexOf('expand_') == 0) {
                    thissuffix = classname.substr(7);
                }
            }
            var summaryclassname = 'summary_' + thissuffix;
            var summary = Y.one('.' + summaryclassname);
            if (summary) {
                summary.hide(false);
            }
            var fullclassname = 'full_' + thissuffix;
            full = Y.one('.' + fullclassname);
            if (full) {
                full.show(false);
                full.one('a.contract_' + thissuffix).focus();
            }
        });
    }
};
