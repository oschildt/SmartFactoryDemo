<?php
session_start();

$messages = "";
$error_field = "";
$error_tab = "";
$files_processed = 0;

$supported_languages = [];
$text_entries = [];
$language_entries = [];
$country_entries = [];

$found_entries = [];
$text_entry_doublicates = [];
$incomplete_translations = [];
$missing_definitions = [];
$never_used = [];

$onclick = "return goto_tab(this.getAttribute('data-id'), false);";

$tabs = [
    "texts" => ["caption" => "Texts", "onclick" => $onclick],
    "languages" => ["caption" => "Languages", "onclick" => $onclick],
    "countries" => ["caption" => "Countries", "onclick" => $onclick],
    "configuration" => ["caption" => "Configuration", "onclick" => $onclick],
    "check" => ["caption" => "Check", "onclick" => ""]
];

$active_tab = "texts";
if (!empty($_REQUEST["tab"])) {
    $active_tab = $_REQUEST["tab"];
}
if (!in_array($active_tab, array_keys($tabs))) {
    $active_tab = "texts";
}

//-----------------------------------------------------------------
function &checkempty(&$var)
{
    return $var;
} // checkempty
//-----------------------------------------------------------------
function load_language_texts()
{
    global $messages;
    
    global $supported_languages;
    global $text_entries;
    global $language_entries;
    global $country_entries;
    
    global $incomplete_translations;
    
    $json = file_get_contents("texts.json");
    if ($json === false) {
        $messages .= "<p style='color:red; font-weight: bold'>Translation file 'localization/texts.json' cannot be loaded!</p>";
        return false;
    }
    
    $json_array = json_decode($json, true);
    if ($json_array === null) {
        $messages .= "<p style='color:red; font-weight: bold'>Translation file 'localization/texts.json' cannot be loaded!</p>";
        return false;
    }
    
    if (!empty($json_array["interface_languages"])) {
        foreach ($json_array["interface_languages"] as $lang_code) {
            $supported_languages[$lang_code] = $lang_code;
        }
    }
    
    if (!empty($json_array["texts"])) {
        $text_entries = $json_array["texts"];
        
        foreach ($text_entries as $text_id => &$translations) {
            foreach ($supported_languages as $lng) {
                if (empty($translations[$lng])) {
                    $incomplete_translations[$text_id][] = $lng;
                }
            }
        }
    }
    
    if (!empty($json_array["languages"])) {
        $language_entries = $json_array["languages"];
    }
    
    if (!empty($json_array["countries"])) {
        $country_entries = $json_array["countries"];
    }
    
    return true;
} // load_language_texts
//-----------------------------------------------------------------
function error_class($name)
{
    global $error_field;
    
    if ($error_field == $name) {
        echo "error_field";
    } else {
        echo "";
    }
} // error_class
//-----------------------------------------------------------------
function save_data()
{
    global $supported_languages;
    global $text_entries;
    global $language_entries;
    global $country_entries;
    
    global $error_field;
    global $error_tab;
    global $messages;
    
    $json_input["interface_languages"] = [];
    
    foreach ($supported_languages as $lang) {
        if (!empty($_REQUEST["delete_supported_languages"]) && in_array($lang, $_REQUEST["delete_supported_languages"])) {
            continue;
        }
        
        $json_input["interface_languages"][] = $lang;
    }
    
    $new_lang = "";
    $new_lang_source = "";
    
    if (!empty($_REQUEST["new_language"]["code"])) {
        if (!empty($supported_languages[$_REQUEST["new_language"]["code"]])) {
            $error_field = "new_language[code]";
            $error_tab = "configuration";
            
            $messages .= "<p style='color:red; font-weight: bold'>The language with the code '" . htmlspecialchars($_REQUEST["new_language"]["code"], ENT_QUOTES) . "' already exists! [<a href=\"javascript:show_error_field('$error_tab', '$error_field')\" class=\"goto\">go to</a>]</p>";
            return false;
        }
        
        $json_input["interface_languages"][] = $_REQUEST["new_language"]["code"];
        
        $new_lang = $_REQUEST["new_language"]["code"];
        
        if (!empty($_REQUEST["new_language"]["copy_source"])) {
            $new_lang_source = $_REQUEST["new_language"]["copy_source"];
        }
    }
    
    // saving languages
    
    $json_input["languages"] = [];
    
    $counter = 0;
    foreach ($language_entries as $code => $entry_data) {
        if (isset($_REQUEST["language_entries"][$counter]["code"])) {
            $code = $_REQUEST["language_entries"][$counter]["code"];
        }
        
        if (empty($code)) {
            $error_field = "language_entries[$counter][code]";
            $error_tab = "languages";
            
            $messages .= "<p style='color:red; font-weight: bold'>The code cannot be empty! [<a href=\"javascript:show_error_field('$error_tab', '$error_field')\" class=\"goto\">go to</a>]</p>";
            return false;
        }
        
        if (!empty($_REQUEST["delete_language_entries"]) && in_array($code, $_REQUEST["delete_language_entries"])) {
            $counter++;
            continue;
        }
        
        $json_input["languages"][$code] = [];
        
        foreach ($supported_languages as $lang) {
            if (isset($_REQUEST["language_entries"][$counter]["langs"][$lang])) {
                $entry_data[$lang] = $_REQUEST["language_entries"][$counter]["langs"][$lang];
            }
            
            if (!empty($_REQUEST["delete_supported_languages"]) && in_array($lang, $_REQUEST["delete_supported_languages"])) {
                continue;
            }
            
            $json_input["languages"][$code][$lang] = $entry_data[$lang];
        }
        
        if (!empty($new_lang)) {
            $json_input["languages"][$code][$new_lang] = "";
            
            if (!empty($new_lang_source)) {
                $json_input["languages"][$code][$new_lang] = checkempty($entry_data[$new_lang_source]);
            }
        }
        
        $counter++;
    }
    
    if (!empty($_REQUEST["new_language_entry"]["code"])) {
        $json_input["languages"][$_REQUEST["new_language_entry"]["code"]] = [];
        
        foreach ($supported_languages as $lang) {
            if (!empty($_REQUEST["delete_supported_languages"]) && in_array($lang, $_REQUEST["delete_supported_languages"])) {
                continue;
            }
            
            $json_input["languages"][$_REQUEST["new_language_entry"]["code"]][$lang] = checkempty($_REQUEST["new_language_entry"]["langs"][$lang]);
        }
        
        if (!empty($new_lang)) {
            $json_input["languages"][$_REQUEST["new_language_entry"]["code"]][$new_lang] = "";
            
            if (!empty($new_lang_source)) {
                $json_input["languages"][$_REQUEST["new_language_entry"]["code"]][$new_lang] = checkempty($_REQUEST["new_language_entry"]["langs"][$new_lang_source]);
            }
        }
    }
    
    ksort($json_input["languages"]);
    
    // saving countries
    
    $json_input["countries"] = [];
    
    $counter = 0;
    foreach ($country_entries as $code => $entry_data) {
        if (isset($_REQUEST["country_entries"][$counter]["code"])) {
            $code = $_REQUEST["country_entries"][$counter]["code"];
        }
        
        if (empty($code)) {
            $error_field = "country_entries[$counter][code]";
            $error_tab = "countries";
            
            $messages .= "<p style='color:red; font-weight: bold'>The code cannot be empty! [<a href=\"javascript:show_error_field('$error_tab', '$error_field')\" class=\"goto\">go to</a>]</p>";
            return false;
        }
        
        if (!empty($_REQUEST["delete_country_entries"]) && in_array($code, $_REQUEST["delete_country_entries"])) {
            $counter++;
            continue;
        }
        
        $json_input["countries"][$code] = [];
        
        foreach ($supported_languages as $lang) {
            if (isset($_REQUEST["country_entries"][$counter]["langs"][$lang])) {
                $entry_data[$lang] = $_REQUEST["country_entries"][$counter]["langs"][$lang];
            }
            
            if (!empty($_REQUEST["delete_supported_languages"]) && in_array($lang, $_REQUEST["delete_supported_languages"])) {
                continue;
            }
            
            $json_input["countries"][$code][$lang] = $entry_data[$lang];
        }
        
        if (!empty($new_lang)) {
            $json_input["countries"][$code][$new_lang] = "";
            
            if (!empty($new_lang_source)) {
                $json_input["countries"][$code][$new_lang] = checkempty($entry_data[$new_lang_source]);
            }
        }
        
        $counter++;
    }
    
    if (!empty($_REQUEST["new_country_entry"]["code"])) {
        $json_input["countries"][$_REQUEST["new_country_entry"]["code"]] = [];
        
        foreach ($supported_languages as $lang) {
            if (!empty($_REQUEST["delete_supported_languages"]) && in_array($lang, $_REQUEST["delete_supported_languages"])) {
                continue;
            }
            
            $json_input["countries"][$_REQUEST["new_country_entry"]["code"]][$lang] = checkempty($_REQUEST["new_country_entry"]["langs"][$lang]);
        }
        
        if (!empty($new_lang)) {
            $json_input["countries"][$_REQUEST["new_country_entry"]["code"]][$new_lang] = "";
            
            if (!empty($new_lang_source)) {
                $json_input["countries"][$_REQUEST["new_country_entry"]["code"]][$new_lang] = checkempty($_REQUEST["new_country_entry"]["langs"][$new_lang_source]);
            }
        }
    }
    
    ksort($json_input["countries"]);
    
    // saving texts
    
    $json_input["texts"] = [];
    
    $counter = 0;
    foreach ($text_entries as $code => $entry_data) {
        if (isset($_REQUEST["text_entries"][$counter]["code"])) {
            $code = $_REQUEST["text_entries"][$counter]["code"];
        }
        
        if (empty($code)) {
            $error_field = "text_entries[$counter][code]";
            $error_tab = "texts";
            
            $messages .= "<p style='color:red; font-weight: bold'>The code cannot be empty! [<a href=\"javascript:show_error_field('$error_tab', '$error_field')\" class=\"goto\">go to</a>]</p>";
            return false;
        }
        
        if (!empty($_REQUEST["delete_text_entries"]) && in_array($code, $_REQUEST["delete_text_entries"])) {
            $counter++;
            continue;
        }
        
        $json_input["texts"][$code] = [];
        
        foreach ($supported_languages as $lang) {
            if (isset($_REQUEST["text_entries"][$counter]["langs"][$lang])) {
                $entry_data[$lang] = $_REQUEST["text_entries"][$counter]["langs"][$lang];
            }
            
            if (!empty($_REQUEST["delete_supported_languages"]) && in_array($lang, $_REQUEST["delete_supported_languages"])) {
                continue;
            }
            
            $json_input["texts"][$code][$lang] = $entry_data[$lang];
        }
        
        if (!empty($new_lang)) {
            $json_input["texts"][$code][$new_lang] = "";
            
            if (!empty($new_lang_source)) {
                $json_input["texts"][$code][$new_lang] = checkempty($entry_data[$new_lang_source]);
            }
        }
        
        $counter++;
    }
    
    if (!empty($_REQUEST["new_text_entry"]["code"])) {
        $json_input["texts"][$_REQUEST["new_text_entry"]["code"]] = [];
        
        foreach ($supported_languages as $lang) {
            if (!empty($_REQUEST["delete_supported_languages"]) && in_array($lang, $_REQUEST["delete_supported_languages"])) {
                continue;
            }
            
            $json_input["texts"][$_REQUEST["new_text_entry"]["code"]][$lang] = checkempty($_REQUEST["new_text_entry"]["langs"][$lang]);
        }
        
        if (!empty($new_lang)) {
            $json_input["texts"][$_REQUEST["new_text_entry"]["code"]][$new_lang] = "";
            
            if (!empty($new_lang_source)) {
                $json_input["texts"][$_REQUEST["new_text_entry"]["code"]][$new_lang] = checkempty($_REQUEST["new_text_entry"]["langs"][$new_lang_source]);
            }
        }
    }
    
    @copy("texts.json", "texts_bak.json");
    
    if (file_put_contents("texts.json", json_encode($json_input, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE)) === false) {
        $messages .= "<p style='color:red; font-weight: bold'>Translation file 'localization/texts.json' cannot be saved!</p>";
        return false;
    }
    
    if (function_exists("apcu_delete")) {
        apcu_delete("dictionary_supported_languages");
        apcu_delete("dictionary_languages");
        apcu_delete("dictionary_countries");
        apcu_delete("dictionary_texts");
    }
    
    $_SESSION["message"] = "<p style='color:green; font-weight: bold'>Data saved successfully!</p>";
    
    return true;
} // save_data
//-----------------------------------------------------------------
$save_result = true;

// we load data always
$load_result = load_language_texts();

if (checkempty($_REQUEST["act"]) == "Cancel") {
    $appendix = "";
    if (!empty($_REQUEST["tab"])) {
        $appendix = "?tab=" . $_REQUEST["tab"];
    }
    
    header("location: edit.php" . $appendix);
    exit;
}

if (checkempty($_REQUEST["act"]) == "Apply") {
    // by check, no saving data
    if ($active_tab == "check" || $save_result = save_data()) {
        $appendix = "";
        if (!empty($_REQUEST["tab"])) {
            $appendix = "?tab=" . $_REQUEST["tab"];
        }
        
        header("location: edit.php" . $appendix);
        exit;
    }
}

if ($load_result && $save_result) {
    $supported_languages = $supported_languages;
    
    $_REQUEST["text_entries"] = [];
    $counter = 0;
    foreach ($text_entries as $code => $texts) {
        $_REQUEST["text_entries"][$counter]["#cdata"] = !empty($texts["#cdata"]);
        $counter++;
    }
}

if (!empty($_SESSION["message"])) {
    $messages .= $_SESSION["message"];
    unset($_SESSION["message"]);
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Translation editor</title>

    <style>
        html,
        body {
            margin: 0px;
        }

        body, p, li, td, th, input, select, option, textarea {
            font-family: Times New Roman;
            font-size: 16px;
        }

        input[type=submit],
        input[type=button] {
            border: 1px solid gray;
            background-color: #dddddd;
            min-width: 70px;
            padding: 3px;
            cursor: pointer;
        }

        input[type=submit]:hover,
        input[type=button]:hover {
            background-color: #cccccc;
        }

        input[type=text] {
            border: 1px solid gray;
            height: 22px;
        }

        select {
            border: 1px solid gray;
            height: 26px;
        }

        a.goto:link,
        a.goto:active,
        a.goto:visited,
        a.goto:focus {
            text-decoration: none;
        }

        .header {
            height: 70px;
            background-color: #5681C2;
            position: fixed;
            top: 0px;
            width: 100%;
            border-bottom: 1px solid lightgray;
        }

        .content_area {
            margin-top: 70px;
            padding: 1px 10px 10px 10px;
        }

        .clear_both {
            clear: both;
        }

        .left_panel {
            float: left;
            color: white;
            font-weight: bold;
            font-size: 22px;
            padding: 5px;
        }

        .right_panel {
            float: right;
            padding: 20px 5px 0px 0px;
        }

        .right_panel input[type=submit] {
            padding: 5px;
            color: white;
            width: 80px;
            border: 1px solid #cccccc;
            background-color: #0078D7;
            cursor: pointer;
        }

        .right_panel input[type=submit]:hover {
            background-color: #103DBF;
        }

        .tab_area {
            position: absolute;
            bottom: -1px;
            left: 5px;
        }

        a.tab:link,
        a.tab:active,
        a.tab:visited,
        a.tab:focus {
            display: block;
            width: 110px;
            float: left;
            text-align: center;
            color: white;
            border: 1px solid lightgray;
            border-right: 0;
            padding: 3px;
            cursor: pointer;
            text-decoration: none;
        }

        a.tab:hover {
            background-color: #0078D7;
        }

        a.tab:nth-last-child(2) {
            border-right: 1px solid lightgray;
        }

        a.active_tab:link,
        a.active_tab:active,
        a.active_tab:visited,
        a.active_tab:focus {
            border: 1px solid white;
            background-color: #0078D7;
            border-bottom: 1px solid lightgray;
            text-decoration: underline;
        }

        a.after_active:link,
        a.after_active:active,
        a.after_active:visited,
        a.after_active:focus {
            border-left: 0;
        }

        .edit_table {
            border-collapse: collapse;
        }

        .edit_table td,
        .edit_table th {
            border: 1px solid gray;
            padding: 3px;
        }

        .edit_table td {
            background-color: #dddddd;
            height: 24px;
            vertical-align: top;
        }

        .edit_table th {
            background-color: #aaaaaa;
            color: white;
        }

        .edit_table input[type=text] {
            width: 250px;
            border: 1px solid gray;
        }

        .edit_table textarea {
            border: 1px solid gray;
            width: 250px;
            height: 50px;
            resize: none;
            margin: 0;
        }

        .edit_table input[type=text].modified_field,
        .edit_table textarea.modified_field {
            border: 1px solid #DEB41C;
            background-color: #FFF14E;
        }

        .edit_table input[type=text].empty_field,
        .edit_table textarea.empty_field,
        .edit_table input[type=text].error_field,
        .edit_table textarea.error_field {
            border: 1px solid red;
            background-color: #FFD9EC;
        }

        .edit_table input[type=text]:hover {
            border: 1px solid #5681C2;
        }

        .edit_table textarea:hover {
            border: 1px solid #5681C2;
        }

        .edit_table .div_input,
        .edit_table .div_textarea {
            padding: 1px 0px 2px 0px;
            background-color: white;
            cursor: text;
            border: 1px solid gray;
        }


        .edit_table .div_input {
            width: 252px;
            height: 21px;
            overflow: hidden;
            white-space: nowrap;
        }

        .edit_table .div_textarea {
            width: 252px;
            height: 47px;
            white-space: pre-wrap;
            overflow: auto;
        }

        .edit_table .div_textarea.empty_field,
        .edit_table .div_input.empty_field,
        .edit_table .div_textarea.error_field,
        .edit_table .div_input.error_field {
            border: 1px solid red;
            background-color: #FFD9EC;
        }

        .edit_table .div_input:hover,
        .edit_table .div_textarea:hover {
            border: 1px solid #5681C2;
        }

        .text_code input[type=text] {
            display: block;
            width: 198px;
        }

        .text_code .div_input {
            display: block;
            width: 200px;
        }

        .short_code input[type=text] {
            display: block;
            width: 48px;
        }

        .short_code .div_input {
            display: block;
            width: 50px;
        }
    </style>

    <script language="JavaScript">
        function replace_input(elm, name) {
            var ti = document.createElement("input");
            ti.type = "text";
            ti.name = name;
            ti.value = elm.getAttribute("data-content");

            ti.onchange = function () {
                check_changes(ti, true);
            };

            if (ti.value == "") ti.classList.add("empty_field");
            else ti.classList.remove("empty_field");

            elm.parentNode.replaceChild(ti, elm);

            ti.focus();
        }

        function replace_textarea(elm, name) {
            var ta = document.createElement("textarea");
            ta.name = name;
            ta.value = elm.getAttribute("data-content");

            ta.onchange = function () {
                check_changes(ta, true);
            };

            if (ta.value == "") ta.classList.add("empty_field");
            else ta.classList.remove("empty_field");

            elm.parentNode.replaceChild(ta, elm);

            if (ta.setSelectionRange) {
                ta.focus();
                ta.setSelectionRange(0, 0);
            } else if (ta.createTextRange) {
                var range = ta.createTextRange();
                range.moveStart('character', 0);
                range.select();
            }
        }

        function goto_tab(tab, nopush) {
            var elm = document.getElementById("tab_" + active_tab);
            if (!elm) return false;

            elm.classList.remove('active_tab');

            if (elm.nextSibling.classList && elm.nextSibling.classList.contains('after_active')) {
                elm.nextSibling.classList.remove('after_active');
            }

            elm = document.getElementById("tab_context_" + active_tab);
            if (!elm) return false;

            elm.style.display = "none";

            elm = document.getElementById("tab_" + tab);
            if (!elm) return false;

            elm.classList.add('active_tab');

            if (elm.nextSibling.classList && elm.nextSibling.classList.contains('tab')) {
                elm.nextSibling.classList.add('after_active');
            }

            elm = document.getElementById("tab_context_" + tab);
            if (!elm) return false;

            elm.style.display = "block";

            active_tab = tab;

            if (!nopush) window.history.pushState({tab: tab}, null, "edit.php?tab=" + tab);

            elm = document.getElementById("tab");
            if (!elm) return false;

            elm.value = active_tab;

            return false;
        } // goto_tab

        function show_error_field(tab, field) {
            goto_tab(tab, false);

            var form = document.getElementById('main_form');
            if (!form) return false;

            try {
                if (form.elements[field]) form.elements[field].focus();
            } catch (e) {
            }
        }

        window.onpopstate = function (e) {
            if (typeof e.state == 'undefined' || !e.state) {
                return true;
            }

            goto_tab(e.state.tab, true);

            if (e.state.tab == "check") window.location.reload();
        };

        var active_tab = "<?php echo($active_tab); ?>";
        window.history.replaceState({tab: active_tab}, null, "edit.php?tab=" + active_tab);

        var modified = <?php echo ($save_result) ? "false" : "true"; ?>;
        var cancelling = false;

        function check_deleted(elm) {
            if (elm.parentNode.parentNode.nodeName != 'TR') return;

            elm.parentNode.parentNode.style.opacity = elm.checked ? "0.2" : "1";
        }

        function check_changes(elm, highlight) {
            modified = true;

            if (elm.type == "textarea" || elm.type == "text") {
                elm.classList.add("modified_field");
            }

            if (highlight) {
                if (elm.value == "") elm.classList.add("empty_field");
                else elm.classList.remove("empty_field");
            }
        }

        function check_submit(form) {
            if (cancelling) {
                modified = false;

                return true;
            }

            var need_ask = false;

            var elms = form.elements["delete_supported_languages[]"];
            if (elms)
                for (var i = 0; i < elms.length; i++) {
                    if (elms[i].checked) need_ask = true;
                }

            elms = form.elements["delete_text_entries[]"];
            if (elms)
                for (var i = 0; i < elms.length; i++) {
                    if (elms[i].checked) need_ask = true;
                }

            elms = form.elements["delete_language_entries[]"];
            if (elms)
                for (var i = 0; i < elms.length; i++) {
                    if (elms[i].checked) need_ask = true;
                }

            elms = form.elements["delete_country_entries[]"];
            if (elms)
                for (var i = 0; i < elms.length; i++) {
                    if (elms[i].checked) need_ask = true;
                }

            var result = true;

            if (need_ask) {
                if (!confirm("Are you sure to delete the selected entries?")) return false;
            }

            modified = false;

            return true;
        }

        window.onbeforeunload = function (e) {
            if (!modified) return undefined;

            var confirmationMessage = 'Are you sure to dismiss your changes?';

            (e || window.event).returnValue = confirmationMessage; //Gecko + IE
            return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
        }
    </script>

</head>
<body>

<form action="edit.php" id="main_form" method="post" onsubmit="return check_submit(this);">
    <input type="hidden" name="tab" id="tab" value="<?php echo(htmlspecialchars($active_tab, ENT_QUOTES)); ?>">

    <div class="header">
        <div class="left_panel">
            Translation editor
        </div>
        <div class="right_panel">
            <input type="submit" name="act" value="Apply">
            <input type="submit" name="act" value="Cancel" onclick="cancelling = confirm('Are you sure to dismiss your changes?'); return cancelling;">
        </div>
        <div class="clear_both">
        </div>

        <div class="tab_area">
            <?php
            $active_class = "";
            $after_active_class = "";
            foreach ($tabs as $tab => $tabinfo):
                
                if (!empty($after_active_class)) {
                    $after_active_class = "";
                }
                
                if (!empty($active_class)) {
                    $active_class = "";
                    $after_active_class = "after_active";
                }
                
                if ($tab == $active_tab) {
                    $active_class = "active_tab";
                }
                ?><a id="tab_<?php echo(htmlspecialchars($tab, ENT_QUOTES)); ?>"
                     class="tab <?php echo($active_class . " " . $after_active_class); ?>"
                     data-id="<?php echo(htmlspecialchars($tab, ENT_QUOTES)); ?>"
                     href="edit.php?tab=<?php echo(htmlspecialchars($tab, ENT_QUOTES)); ?>"
                     onclick="<?php echo($tabinfo["onclick"]); ?>"><?php echo(htmlspecialchars($tabinfo["caption"], ENT_QUOTES)); ?></a><?php endforeach; ?>


            <div class="clear_both"></div>
        </div>
    </div>

    <div class="content_area">

        <!-- BEGIN: texts -->
        <div id="tab_context_texts" class="tab_content" style="display: <?php echo ($active_tab == "texts") ? "block" : "none"; ?>">

            <h2>Text translations</h2>
            
            <?php echo($messages); ?>

            <table class="edit_table">
                <tr>
                    <th style="font-weight: normal">✘</th>
                    <th>Code</th>
                    
                    <?php foreach ($supported_languages as $lang):
                        $caption = $lang;
                        if (!empty($language_entries[$lang]["en"])) {
                            $caption = $language_entries[$lang]["en"] . " [" . $lang . "]";
                        }
                        ?>
                        <th><?php echo(htmlspecialchars($caption, ENT_QUOTES)); ?></th>
                    <?php endforeach; ?>

                </tr>

                <tr>
                    <td></td>
                    <td class="text_code">
                        <?php
                        $class = "";
                        if ($error_field == "new_text_entry[code]") {
                            $class = "error_field";
                        }
                        ?>
                        <input type="text" class="<?php echo($class); ?>" name="new_text_entry[code]" value="<?php echo(htmlspecialchars(checkempty($_REQUEST["new_text_entry"]["code"]), ENT_QUOTES)); ?>" onchange="check_changes(this, true)" placeholder="new">
                    </td>
                    
                    <?php
                    foreach ($supported_languages as $lang):
                        $class = "";
                        if ($error_field == "new_text_entry[langs][$lang]") {
                            $class = "error_field";
                        }
                        ?>
                        <td><textarea class="<?php echo($class); ?>" name="new_text_entry[langs][<?php echo(htmlspecialchars($lang, ENT_QUOTES)); ?>]" onchange="check_changes(this, true)"><?php echo(htmlspecialchars(checkempty($_REQUEST["new_text_entry"]["langs"][$lang]), ENT_QUOTES)); ?></textarea></td>
                    <?php endforeach; ?>
                </tr>
                
                <?php
                $cols = 2 + count($supported_languages);
                ?>

                <tr>
                    <td colspan="<?php echo($cols); ?>" style="text-align: right"><input type="submit" name="act" value="Apply"></td>
                </tr>
                
                <?php if (count($text_entries) > 0): ?>

                    <tr>
                        <th style="font-weight: normal">✘</th>
                        <th>Code</th>
                        
                        <?php foreach ($supported_languages as $lang):
                            $caption = $lang;
                            if (!empty($language_entries[$lang]["en"])) {
                                $caption = $language_entries[$lang]["en"] . " [" . $lang . "]";
                            }
                            ?>
                            <th><?php echo(htmlspecialchars($caption, ENT_QUOTES)); ?></th>
                        <?php endforeach; ?>

                    </tr>
                    
                    <?php
                    $counter = 0;
                    foreach ($text_entries as $code => $entry_data):
                        
                        if (isset($_REQUEST["text_entries"][$counter]["code"])) {
                            $code = $_REQUEST["text_entries"][$counter]["code"];
                        }
                        ?>

                        <tr>
                            <td><input type="checkbox" name="delete_text_entries[]" value="<?php echo(htmlspecialchars($code)); ?>" title="Click to delete" onchange="check_deleted(this)"></td>
                            <td class="text_code">
                                
                                <?php
                                $class = "";
                                if (empty($_REQUEST["text_entries"][$counter]["code"])) {
                                    $class = "empty_field";
                                }
                                if ($error_field == "text_entries[$counter][code]") {
                                    $class = "error_field";
                                }
                                ?>
                                
                                <?php if (isset($_REQUEST["text_entries"][$counter]["code"])): ?>
                                    <input type="text" class="<?php echo($class); ?>" name="text_entries[<?php echo($counter); ?>][code]" value="<?php echo(htmlspecialchars($code, ENT_QUOTES)); ?>" onchange="check_changes(this, true)">
                                <?php else:
                                    $class = "";
                                    if (empty($code)) {
                                        $class = "empty_field";
                                    }
                                    ?>
                                    <div class="div_input <?php echo($class); ?>" title="Click to edit" data-content="<?php echo(htmlspecialchars($code, ENT_QUOTES)); ?>" onclick="replace_input(this, 'text_entries[<?php echo($counter); ?>][code]')"><?php echo(htmlspecialchars($code, ENT_QUOTES)); ?></div>
                                <?php endif; ?>
                            </td>
                            
                            <?php
                            foreach ($supported_languages as $lang):
                                $class = "";
                                if (empty($_REQUEST["text_entries"][$counter]["langs"][$lang])) {
                                    $class = "empty_field";
                                }
                                if ($error_field == "text_entries[$counter][langs][$lang]") {
                                    $class = "error_field";
                                }
                                
                                $translation = checkempty($entry_data["lang"]);
                                ?>
                                <td>
                                    <?php if (isset($_REQUEST["text_entries"][$counter]["langs"][$lang])): ?>
                                        <textarea class="<?php echo($class); ?>" name="text_entries[<?php echo($counter); ?>][langs][<?php echo(htmlspecialchars($lang, ENT_QUOTES)); ?>]" onchange="check_changes(this, true)"><?php echo(htmlspecialchars(checkempty($_REQUEST["text_entries"][$counter]["langs"][$lang]), ENT_QUOTES)); ?></textarea>
                                    <?php else:
                                        $class = "";
                                        if (empty($entry_data[$lang])) {
                                            $class = "empty_field";
                                        }
                                        ?>
                                        <div class="div_textarea <?php echo($class); ?>" title="Click to edit" data-content="<?php echo(htmlspecialchars(checkempty($entry_data[$lang]), ENT_QUOTES)); ?>" onclick="replace_textarea(this, 'text_entries[<?php echo($counter); ?>][langs][<?php echo(htmlspecialchars($lang, ENT_QUOTES)); ?>]')"><?php echo(htmlspecialchars(checkempty($entry_data[$lang]), ENT_QUOTES)); ?></div>
                                    <?php endif; ?>
                                </td>
                            <?php
                            endforeach;
                            ?>
                        </tr>
                        
                        <?php
                        $counter++;
                    endforeach;
                    ?>
                
                <?php endif; ?>

            </table>

            <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

        </div>
        <!-- END: texts -->

        <!-- BEGIN: languages -->
        <div id="tab_context_languages" class="tab_content" style="display: <?php echo ($active_tab == "languages") ? "block" : "none"; ?>">
            <h2>Language name translations</h2>
            
            <?php echo($messages); ?>

            <table class="edit_table">
                <tr>
                    <th style="font-weight: normal">✘</th>
                    <th>Code</th>
                    
                    <?php foreach ($supported_languages as $lang):
                        $caption = $lang;
                        if (!empty($language_entries[$lang]["en"])) {
                            $caption = $language_entries[$lang]["en"] . " [" . $lang . "]";
                        }
                        ?>
                        <th><?php echo(htmlspecialchars($caption, ENT_QUOTES)); ?></th>
                    <?php endforeach; ?>

                </tr>

                <tr>
                    <td></td>
                    <td class="short_code">
                        <?php
                        $class = "";
                        if ($error_field == "new_language_entry[code]") {
                            $class = "error_field";
                        }
                        ?>

                        <input type="text" class="<?php echo($class); ?>" name="new_language_entry[code]" value="<?php echo(htmlspecialchars(checkempty($_REQUEST["new_language_entry"]["code"]), ENT_QUOTES)); ?>" onchange="check_changes(this, true)" placeholder="new">
                    </td>
                    
                    <?php
                    foreach ($supported_languages as $lang):
                        $class = "";
                        if ($error_field == "new_language_entry[langs][$lang]") {
                            $class = "error_field";
                        }
                        ?>
                        <td><input type="text" class="<?php echo($class); ?>" name="new_language_entry[langs][<?php echo(htmlspecialchars($lang, ENT_QUOTES)); ?>]" onchange="check_changes(this, true)" value="<?php echo(htmlspecialchars(checkempty($_REQUEST["new_language_entry"]["langs"][$lang]), ENT_QUOTES)); ?>"></td>
                    <?php endforeach; ?>
                </tr>
                
                <?php
                $cols = 2 + count($supported_languages);
                ?>

                <tr>
                    <td colspan="<?php echo($cols); ?>" style="text-align: right"><input type="submit" name="act" value="Apply"></td>
                </tr>
                
                <?php if (count($language_entries) > 0): ?>
                    
                    <?php
                    $counter = 0;
                    foreach ($language_entries as $code => $entry_data):
                        if (isset($_REQUEST["language_entries"][$counter]["code"])) {
                            $code = $_REQUEST["language_entries"][$counter]["code"];
                        }
                        ?>

                        <tr>
                            <td><input type="checkbox" name="delete_language_entries[]" value="<?php echo(htmlspecialchars($code, ENT_QUOTES)); ?>" title="Click to delete" onchange="check_deleted(this)"></td>
                            <td class="short_code">
                                <?php
                                $class = "";
                                if (empty($_REQUEST["language_entries"][$counter]["code"])) {
                                    $class = "empty_field";
                                }
                                if ($error_field == "language_entries[$counter][code]") {
                                    $class = "error_field";
                                }
                                ?>
                                
                                <?php if (isset($_REQUEST["language_entries"][$counter]["code"])): ?>
                                    <input type="text" class="<?php echo($class); ?>" name="language_entries[<?php echo($counter); ?>][code]" value="<?php echo(htmlspecialchars(checkempty($_REQUEST["language_entries"][$counter]["code"]), ENT_QUOTES)); ?>" onchange="check_changes(this, true)">
                                <?php else:
                                    $class = "";
                                    if (empty($code)) {
                                        $class = "empty_field";
                                    }
                                    ?>
                                    <div class="div_input <?php echo($class); ?>" title="Click to edit" data-content="<?php echo(htmlspecialchars($code, ENT_QUOTES)); ?>" onclick="replace_input(this, 'language_entries[<?php echo($counter); ?>][code]')"><?php echo(htmlspecialchars($code, ENT_QUOTES)); ?></div>
                                <?php endif; ?>
                            </td>
                            
                            <?php
                            foreach ($supported_languages as $lang):
                                $class = "";
                                if (empty($_REQUEST["language_entries"][$counter]["langs"][$lang])) {
                                    $class = "empty_field";
                                }
                                if ($error_field == "language_entries[$counter][langs][$lang]") {
                                    $class = "error_field";
                                }
                                ?>
                                <td>
                                    <?php if (isset($_REQUEST["language_entries"][$counter]["langs"][$lang])): ?>
                                        <input type="text" class="<?php echo($class); ?>" name="language_entries[<?php echo($counter); ?>][langs][<?php echo(htmlspecialchars($lang, ENT_QUOTES)); ?>]" onchange="check_changes(this, true)" value="<?php echo(htmlspecialchars(checkempty($_REQUEST["language_entries"][$counter]["langs"][$lang]), ENT_QUOTES)); ?>">
                                    <?php else:
                                        $class = "";
                                        if (empty($entry_data[$lang])) {
                                            $class = "empty_field";
                                        }
                                        ?>
                                        <div class="div_input <?php echo($class); ?>" title="Click to edit" data-content="<?php echo(htmlspecialchars($entry_data[$lang], ENT_QUOTES)); ?>" onclick="replace_input(this, 'language_entries[<?php echo($counter); ?>][langs][<?php echo(htmlspecialchars($lang, ENT_QUOTES)); ?>]')"><?php echo(htmlspecialchars($entry_data[$lang], ENT_QUOTES)); ?></div>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        
                        <?php
                        $counter++;
                    endforeach;
                    ?>
                
                <?php endif; ?>

            </table>

            <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

        </div>
        <!-- END: languages -->

        <!-- BEGIN: countries -->
        <div id="tab_context_countries" class="tab_content" style="display: <?php echo ($active_tab == "countries") ? "block" : "none"; ?>">
            <h2>Country name translations</h2>
            
            <?php echo($messages); ?>

            <table class="edit_table">
                <tr>
                    <th style="font-weight: normal">✘</th>
                    <th>Code</th>
                    
                    <?php foreach ($supported_languages as $lang):
                        $caption = $lang;
                        if (!empty($language_entries[$lang]["en"])) {
                            $caption = $language_entries[$lang]["en"] . " [" . $lang . "]";
                        }
                        ?>
                        <th><?php echo(htmlspecialchars($caption, ENT_QUOTES)); ?></th>
                    <?php endforeach; ?>

                </tr>

                <tr>
                    <td></td>
                    <td class="short_code">
                        <?php
                        $class = "";
                        if ($error_field == "new_country_entry[code]") {
                            $class = "error_field";
                        }
                        ?>
                        <input type="text" class="<?php echo($class); ?>" name="new_country_entry[code]" value="<?php echo(htmlspecialchars(checkempty($_REQUEST["new_country_entry"]["code"]), ENT_QUOTES)); ?>" onchange="check_changes(this, true)" placeholder="new">
                    </td>
                    
                    <?php
                    foreach ($supported_languages as $lang):
                        $class = "";
                        if ($error_field == "new_country_entry[langs][$lang]") {
                            $class = "error_field";
                        }
                        ?>
                        <td><input type="text" class="<?php echo($class); ?>" name="new_country_entry[langs][<?php echo(htmlspecialchars($lang, ENT_QUOTES)); ?>]" onchange="check_changes(this, true)" value="<?php echo(htmlspecialchars(checkempty($_REQUEST["new_country_entry"]["langs"][$lang]), ENT_QUOTES)); ?>"></td>
                    <?php endforeach; ?>
                </tr>
                
                <?php
                $cols = 2 + count($supported_languages);
                ?>

                <tr>
                    <td colspan="<?php echo($cols); ?>" style="text-align: right"><input type="submit" name="act" value="Apply"></td>
                </tr>
                
                <?php if (count($country_entries) > 0): ?>
                    
                    <?php
                    $counter = 0;
                    foreach ($country_entries as $code => $entry_data):
                        if (isset($_REQUEST["country_entries"][$counter]["code"])) {
                            $code = $_REQUEST["country_entries"][$counter]["code"];
                        }
                        ?>

                        <tr>
                            <td><input type="checkbox" name="delete_country_entries[]" value="<?php echo(htmlspecialchars($code, ENT_QUOTES)); ?>" title="Click to delete" onchange="check_deleted(this)"></td>
                            <td class="short_code">
                                <?php
                                $class = "";
                                if (empty($_REQUEST["country_entries"][$counter]["code"])) {
                                    $class = "empty_field";
                                }
                                if ($error_field == "country_entries[$counter][code]") {
                                    $class = "error_field";
                                }
                                ?>
                                
                                <?php if (isset($_REQUEST["country_entries"][$counter]["code"])): ?>
                                    <input type="text" class="<?php echo($class); ?>" name="country_entries[<?php echo($counter); ?>][code]" value="<?php echo(htmlspecialchars(checkempty($_REQUEST["country_entries"][$counter]["code"]), ENT_QUOTES)); ?>" onchange="check_changes(this, true)">
                                <?php else:
                                    $class = "";
                                    if (empty($code)) {
                                        $class = "empty_field";
                                    }
                                    ?>
                                    <div class="div_input <?php echo($class); ?>" title="Click to edit" data-content="<?php echo(htmlspecialchars($code, ENT_QUOTES)); ?>" onclick="replace_input(this, 'country_entries[<?php echo($counter); ?>][code]')"><?php echo(htmlspecialchars($code, ENT_QUOTES)); ?></div>
                                <?php endif; ?>
                            </td>
                            
                            <?php
                            foreach ($supported_languages as $lang):
                                $class = "";
                                if (empty($_REQUEST["country_entries"][$counter]["langs"][$lang])) {
                                    $class = "empty_field";
                                }
                                if ($error_field == "country_entries[$counter][code]") {
                                    $class = "error_field";
                                }
                                ?>
                                <td>
                                    <?php if (isset($_REQUEST["country_entries"][$counter]["langs"][$lang])): ?>
                                        <input type="text" class="<?php echo($class); ?>" name="country_entries[<?php echo($counter); ?>][langs][<?php echo(htmlspecialchars($lang, ENT_QUOTES)); ?>]" onchange="check_changes(this, true)" value="<?php echo(htmlspecialchars(checkempty($_REQUEST["country_entries"][$counter]["langs"][$lang]), ENT_QUOTES)); ?>">
                                    <?php else:
                                        $class = "";
                                        if (empty($entry_data[$lang])) {
                                            $class = "empty_field";
                                        }
                                        ?>
                                        <div class="div_input <?php echo($class); ?>" title="Click to edit" data-content="<?php echo(htmlspecialchars($entry_data[$lang], ENT_QUOTES)); ?>" onclick="replace_input(this, 'country_entries[<?php echo($counter); ?>][langs][<?php echo(htmlspecialchars($lang, ENT_QUOTES)); ?>]')"><?php echo(htmlspecialchars($entry_data[$lang], ENT_QUOTES)); ?></div>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        
                        <?php
                        $counter++;
                    endforeach;
                    ?>
                
                <?php endif; ?>

            </table>

            <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

        </div>
        <!-- END: countries -->

        <!-- BEGIN: configuration -->
        <div id="tab_context_configuration" class="tab_content" style="display: <?php echo ($active_tab == "configuration") ? "block" : "none"; ?>">

            <h2>Supported languages</h2>
            
            <?php echo($messages); ?>

            <table class="edit_table">
                <tr>
                    <th style="font-weight: normal">✘</th>
                    <th>Code</th>
                    <th>Language</th>
                </tr>
                
                <?php if (count($supported_languages) > 0): ?>
                    
                    <?php foreach ($supported_languages as $lang):
                        $caption = $lang;
                        if (!empty($language_entries[$lang]["en"])) {
                            $caption = $language_entries[$lang]["en"] . " [" . $lang . "]";
                        }
                        ?>

                        <tr>
                            <td><?php if ($lang != "en"): ?><input type="checkbox" name="delete_supported_languages[]" value="<?php echo(htmlspecialchars($lang, ENT_QUOTES)); ?>" title="Click to delete" onchange="check_deleted(this)"><?php endif; ?></td>
                            <td><?php echo(htmlspecialchars($lang, ENT_QUOTES)); ?></td>
                            <td><?php echo(htmlspecialchars($caption, ENT_QUOTES)); ?></td>
                        </tr>
                    
                    <?php endforeach; ?>
                
                <?php endif; ?>

                <tr>
                    <td></td>
                    <td><input type="text" class="<?php error_class("new_language[code]"); ?>" style="width:60px" name="new_language[code]" value="<?php echo(htmlspecialchars(checkempty($_REQUEST["new_language"]["code"]), ENT_QUOTES)); ?>" placeholder="new"></td>
                    <td>
                        <select name="new_language[copy_source]">
                            <option value="">-- copy from --</option>
                            <?php foreach ($supported_languages as $lang):
                                $caption = $lang;
                                if (!empty($language_entries[$lang]["en"])) {
                                    $caption = $language_entries[$lang]["en"] . " [" . $lang . "]";
                                }
                                
                                $selected = (checkempty($_REQUEST["new_language"]["copy_source"]) == $lang) ? "selected" : "";
                                ?>
                                <option value="<?php echo(htmlspecialchars($lang, ENT_QUOTES)); ?>" <?php echo($selected); ?>><?php echo(htmlspecialchars($caption, ENT_QUOTES)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td colspan="3" style="text-align: right"><input type="submit" name="act" value="Apply"></td>
                </tr>

            </table>

            <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

        </div>
        <!-- END: configuration -->

        <!-- BEGIN: check -->
        <div id="tab_context_check" class="tab_content" style="display: <?php echo ($active_tab == "check") ? "block" : "none"; ?>">
            
            <?php echo($messages); ?>
            
            <?php
            //-----------------------------------------------------------------
            function process_file($file)
            {
                global $found_entries;
                global $text_entries;
                global $missing_definitions;
                
                $handle = fopen($file, "rt");
                if (!$handle) {
                    echo "<p style='color:red; font-weight: bold'>Error by opening file '$file' for reading!</p>";
                    return false;
                }
                
                $length = filesize($file);
                
                if ($length == 0) {
                    return true;
                }
                
                $contents = fread($handle, $length);
                fclose($handle);
                
                if (preg_match_all("/[^a-zA-Z0-9_]text\\(\\s*(\"([^\"]+)\"|'([^']+)')/", $contents, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $nr => $match) {
                        $entry = "";
                        
                        if (!empty($match[2])) {
                            $entry = $match[2];
                        }
                        if (!empty($match[3])) {
                            $entry = $match[3];
                        }
                        
                        if (empty($entry)) {
                            continue;
                        }
                        
                        $found_entries[$entry] = $entry;
                        
                        if (empty($text_entries[$entry])) {
                            $f = str_replace("../", "", $file);
                            
                            $missing_definitions[$entry][$f] = $f;
                        }
                        
                    } // foreach
                } // if preg
                
                return true;
            } // process_file
            //-----------------------------------------------------------------
            function process_dir($dir)
            {
                global $files_processed;
                
                $files = scandir($dir);
                foreach ($files as $file) {
                    if ($file == "." || $file == "..") {
                        continue;
                    }
                    
                    if (is_dir($dir . $file)) {
                        if (!process_dir($dir . $file . "/")) {
                            return false;
                        }
                        continue;
                    }
                    
                    $path_parts = pathinfo($dir . $file);
                    if (!empty($path_parts['extension']) && $path_parts['extension'] == "php") {
                        $files_processed++;
                        if (!process_file($dir . $file)) {
                            return false;
                        }
                    }
                }
                
                return true;
            } // process_dir
            //-----------------------------------------------------------------
            
            $success = false;
            
            if ($load_result && $active_tab == "check") {
                echo "<h2>Check results</h2>";
                
                $dir = "../";
                if (process_dir($dir)) {
                    echo "Supported languages: " . implode(", ", $supported_languages) . "<br>";
                    echo "Entries found: " . count($text_entries) . "<br>";
                    echo "Files processed: " . $files_processed . "<br>";
                    
                    $success = true;
                    
                    if (!empty($text_entry_doublicates)) {
                        $success = false;
                        
                        echo("<p style='color:maroon; font-weight: bold'>Doublicates found!</p>");
                        
                        foreach ($text_entry_doublicates as $dbl) {
                            echo($dbl . "<br>");
                        }
                    }
                    
                    if (!empty($incomplete_translations)) {
                        $success = false;
                        
                        echo("<p style='color:maroon; font-weight: bold'>Incomplete translations found!</p>");
                        
                        foreach ($incomplete_translations as $text => $langs) {
                            echo($text . ": " . implode(", ", $langs) . "<br>");
                        }
                    }
                    
                    if (!empty($missing_definitions)) {
                        $success = false;
                        
                        echo("<p style='color:maroon; font-weight: bold'>Missing definitions found!</p>");
                        
                        foreach ($missing_definitions as $text => $files) {
                            echo($text . ": " . implode(", ", $files) . "<br>");
                        }
                    }
                    
                    $never_used = array_diff(array_keys($text_entries), $found_entries);
                    if (!empty($never_used)) {
                        $success = false;
                        
                        echo("<p style='color:maroon; font-weight: bold'>These definitions are never used!</p>");
                        
                        foreach ($never_used as $text) {
                            echo($text . "<br>");
                        }
                    }
                }
            }
            
            if ($success) {
                echo("<p style='color:green; font-weight: bold'>All is fine!</p>");
            }
            ?>
        </div>
        <!-- END: check -->

    </div>

</form>
</body>
</html>