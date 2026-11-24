<?php
namespace MyApplication;

require "../vendor/autoload.php";

use SmartFactory\Interfaces\ILanguageManager;

use function SmartFactory\singleton;
use function SmartFactory\text;
?><!DOCTYPE html>
<html lang="en">

<head>
    <title>Localization</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="css/examples.css" type="text/css" />
    <script src="js/examples.js" defer></script>
</head>

<body>

    <div class="header"></div>
    <div class="content">

        <div class="left_links"><a href="09.localization.php?language=en">en</a> &middot;
            <a href="09.localization.php?language=de">de</a> &middot;
            <a href="09.localization.php?language=ru">ru</a>
        </div>
        <div class="right_links">
            <p><a href="index.php">List of examples</a></p>
        </div>
        <div style="clear: both;"></div>

        <h1>Localization</h1>

        <p>This example demonstrates how to switch languages, retrieve translated text, and work with language and
            country names using <i>LanguageManager</i>.</p>

        <pre class="code">
$lmanager = singleton(ILanguageManager::class);

$langs = $lmanager->getSupportedLanguages();
</pre>

        <?php
        $lmanager = singleton(ILanguageManager::class);

        $langs = $lmanager->getSupportedLanguages();

        echo "<p>Supported langauges</p>";
        echo "<pre>";
        print_r($langs);
        echo "</pre>";

        echo "<p>current language: " . $lmanager->getCurrentLanguage() . "</p>";

        echo "<p>";
        echo "Translation 'DatabaseName': " . $lmanager->text('DatabaseName') . "<br>";
        echo "Translation 'DatabaseName (short function)': " . text('DatabaseName') . "<br>";
        echo "hasTranslation 'DatabaseName': " . $lmanager->hasTranslation('DatabaseName') . "<br>";
        echo "</p>";

        echo "<p>";
        echo "Translation 'Forms': " . $lmanager->text('Forms') . "<br>";
        echo "Translation 'Forms (short function)': " . text('Forms') . "<br>";
        echo "hasTranslation 'Forms': " . $lmanager->hasTranslation('Forms') . "<br>";
        echo "</p>";

        echo "<p>";
        echo "Translation 'wrox_pox': " . $lmanager->text('wrox_pox') . "<br>";
        echo "hasTranslation 'wrox_pox': " . $lmanager->hasTranslation('wrox_pox') . "<br>";
        echo "</p>";

        echo "<p>";
        echo "Language name 'fr': " . $lmanager->getLanguageName('fr') . "<br>";
        echo "validateLanguageCode 'fr': " . $lmanager->hasLanguageName('fr') . "<br>";
        echo "getLanguageCode 'Russian': " . $lmanager->getLanguageCode('Russian') . "<br>";
        echo "</p>";

        echo "<p>";
        echo "Language name 'wx': " . $lmanager->getLanguageName('wx') . "<br>";
        echo "validateLanguageCode 'wx': " . $lmanager->hasLanguageName('wx') . "<br>";
        echo "getLanguageCode 'Woxxy': " . $lmanager->getLanguageCode('Woxxy') . "<br>";
        echo "</p>";

        echo "<p>";
        echo "Country name 'DE': " . $lmanager->getCountryName('DE') . "<br>";
        echo "validateCountryCode 'DE': " . $lmanager->hasCountryName('DE') . "<br>";
        echo "getCountryCode 'Deutschland': " . $lmanager->getCountryCode('Deutschland') . "<br>";
        echo "</p>";

        echo "<p>";
        echo "Country name 'WX': " . $lmanager->getCountryName('WX') . "<br>";
        echo "validateCountryCode 'WX': " . $lmanager->hasCountryName('WX') . "<br>";
        echo "getCountryCode 'Woxxy': " . $lmanager->getCountryCode('Woxxy') . "<br>";
        echo "</p>";
        ?>

        <h2>Lanugage list (top 20)</h2>

        <?php
        $language_list = [];
        $lmanager->getLanguageList($language_list, "", ["en", "de", "fr", "ru"]);
        echo "<pre>";
        print_r(array_slice($language_list, 0, 20));
        echo "...";
        echo "</pre>";
        ?>

        <h2>Country list (top 20)</h2>

        <?php
        $country_list = [];
        $lmanager->getCountryList($country_list, "", ["GB", "DE", "FR", "RU"]);
        echo "<pre>";
        print_r(array_slice($country_list, 0, 20));
        echo "...";
        echo "</pre>";
        ?>

        <?php
        report_messages();
        ?>

    </div>

    <div class="_sys_load_lightbox" id="sys_progress_indicator" role="status" aria-live="polite" aria-hidden="true">
        <div>
            <div>
                <div class="progress_caption">Loading data ...</div>
                <div id="loading_indicator"><img src="images/loading.gif" width="220" height="19" alt=""></div>
            </div>
        </div>
    </div>

</body>

</html>
