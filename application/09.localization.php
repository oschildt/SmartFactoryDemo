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

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Localization</h2>

<a href="10.localization.php?language=en">en</a>
<a href="10.localization.php?language=de">de</a>
<a href="10.localization.php?language=ru">ru</a>
<br><br>

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
echo "validateLanguageCode 'fr': " . $lmanager->validateLanguageCode('fr') . "<br>";
echo "getLanguageCode 'Russian': " . $lmanager->getLanguageCode('Russian') . "<br>";
echo "</p>";

echo "<p>";
echo "Language name 'wx': " . $lmanager->getLanguageName('wx') . "<br>";
echo "validateLanguageCode 'wx': " . $lmanager->validateLanguageCode('wx') . "<br>";
echo "getLanguageCode 'Woxxy': " . $lmanager->getLanguageCode('Woxxy') . "<br>";
echo "</p>";

echo "<p>";
echo "Country name 'DE': " . $lmanager->getCountryName('DE') . "<br>";
echo "validateCountryCode 'DE': " . $lmanager->validateCountryCode('DE') . "<br>";
echo "getCountryCode 'Deutschland': " . $lmanager->getCountryCode('Deutschland') . "<br>";
echo "</p>";

echo "<p>";
echo "Country name 'WX': " . $lmanager->getCountryName('WX') . "<br>";
echo "validateCountryCode 'WX': " . $lmanager->validateCountryCode('WX') . "<br>";
echo "getCountryCode 'Woxxy': " . $lmanager->getCountryCode('Woxxy') . "<br>";
echo "</p>";
?>

<h3>Lanugage list (top 20)</h3>

<?php
$language_list = [];
$lmanager->getLanguageList($language_list, "", ["en", "de", "fr", "ru"]);
echo "<pre>";
print_r(array_slice($language_list, 0, 20));
echo "...";
echo "</pre>";
?>

<h3>Country list (top 20)</h3>

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

</body>
</html>