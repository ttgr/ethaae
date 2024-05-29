// https://github.com/Choices-js/Choices?tab=readme-ov-file#methods

window.Joomla = window.Joomla || {};

(function (window, Joomla) {
    Joomla.toggleField = function (id, task, field) {

        var f = document.adminForm, i = 0, cbx, cb = f[ id ];

        if (!cb) return false;

        while (true) {
            cbx = f[ 'cb' + i ];

            if (!cbx) break;

            cbx.checked = false;
            i++;
        }

        var inputField   = document.createElement('input');

        inputField.type  = 'hidden';
        inputField.name  = 'field';
        inputField.value = field;
        f.appendChild(inputField);

        cb.checked = true;
        f.boxchecked.value = 1;
        Joomla.submitform(task);

        return false;
    };
})(window, Joomla);



js = jQuery.noConflict();

var getDepartments = function(val) {
    setSelectedUnit(val);
    url = document.getElementById("departements-type-url").value+'&institutionid='+  val,
        Joomla.removeMessages(),
        js.ajax({
            method: "POST",
            url: url,
            dataType: "json"
        }).fail(function(e) {
            console.log(e);
        }).done(function(e) {
            regExp = /\[(.*?)\]/g;
            matches = e.data.match(regExp);

            clearOptionList("jform_fk_deprtement_id");
            el = document.getElementById("jform_fk_deprtement_id");
            const fancySelect = el.closest('joomla-field-fancy-select');
            m =matches[0].substring(1, matches[0].length - 1);
            arr = m.split(",");
            options = '';
            js.each(arr, function(key,value) {
                t = value.split("|");
                options += '<option value="' + t[0] + '">' + t[1] + '</option>';
                const option = {};
                option.innerText = t[1];
                option.id = t[0];
                fancySelect.choicesInstance.setChoices([option], 'id', 'innerText', false);
            });
            fancySelect.choicesInstance.setChoiceByValue('0');
            js(el).html(options);
            js(el).trigger("liszt:updated");
        })
};


var getProgrammes = function(val) {
        setSelectedUnit(val);
        url = document.getElementById("programmes-type-url").value+'&department='+  val;
        Joomla.removeMessages();
        js.ajax({
            method: "POST",
            url: url,
            dataType: "json"
        }).fail(function(e) {
            console.log(e);
        }).done(function(e) {
            regExp = /\[(.*?)\]/g;
            matches = e.data.match(regExp);
            clearOptionList("jform_fk_programme_id");

            el = document.getElementById("jform_fk_programme_id");
            const fancySelect = el.closest('joomla-field-fancy-select');
            m =matches[0].substring(1, matches[0].length - 1);
            arr = m.split(",");
            options = '';
            js.each(arr, function(key,value) {
                t = value.split("|");
                options += '<option value="' + t[0] + '">' + t[1] + '</option>';
                const option = {};
                option.innerText = t[1];
                option.id = t[0];
                fancySelect.choicesInstance.setChoices([option], 'id', 'innerText', false);
            });
            fancySelect.choicesInstance.setChoiceByValue('0');
            js(el).html(options);
            js(el).trigger("liszt:updated");
        })
};



var setSelectedUnit  = function (val) {
    document.getElementById("jform_fk_unit_id").value = val;
}

var onReportTypeChange = function () {
    setSelectedUnit(0);
    setFancySelectValue("jform_fk_institute_id",'0');
    clearOptionList("jform_fk_programme_id");
    clearOptionList("jform_fk_deprtement_id");
}

var setFancySelectValue = function (obj_name,val) {
    el = document.getElementById(obj_name);
    js(el).empty();
    js(el).html("");
    js(el).trigger("liszt:updated");
    const fancySelect = el.closest('joomla-field-fancy-select');
    fancySelect.choicesInstance.setChoiceByValue(val);
}

var clearOptionList = function(obj_name) {
    el = document.getElementById(obj_name);
    js(el).empty();
    js(el).html("");
    js(el).trigger("liszt:updated");
    const fancySelect = el.closest('joomla-field-fancy-select');
    fancySelect.choicesInstance.clearChoices();
    fancySelect.choicesInstance.removeActiveItems();
};
