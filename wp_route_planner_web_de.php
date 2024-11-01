<?php
/*
Plugin Name: WP Routenplaner
Plugin URI: http://route.web.de
Description: Includes the web.de <a href="http://route.web.de">route planner</a> to your blog.
Version: 1.0.2
Author: Jens Altmann
Author URI: http://www.complex-economy.de/
License: GPLv3
         You are not allowed to remove or change the text messages.
*/

if (!class_exists("WPRoutePlannerWebDe"))
{
  class WPRoutePlannerWebDe
  {
    var $countries = NULL;

    //Konstruktor des Plugins. Lädt die Hooks und Initialisiert die Variablen
    function WPRoutePlannerWebDe()
    {
      //Activate Plugin Hook
      register_activation_hook( __FILE__, array( $this, 'install' ) );


      $this->addActions();
      $this->init();
    }

    //Initialisierung der Variablen
    function init()
    {
      //load locale text
      //load_plugin_textdomain('wp_route_planner_web_de', false, basename(dirname( __FILE__ )).'/locale');

      //init countries
      $this->loadCountries();
    }

    //Install Funktion zur Erstellung eines festen Textes
    function install()
    {
      //setting text
      $optionName = 'wp_route_planner_web_de_text';

      //text already existing?
      $text = get_option($optionName);
      if ($text == false) //text does not exist
      {
        $lines = array();
        $lines[] = 'Planen Sie ihre Reise mit dem <a href="http://route.web.de/">Routeplaner von WEB.DE</a>';
        $lines[] = 'Der WEB.DE Routenplaner auch für <a http://route.web.de/?template=routing">Ihre Homepage</a>.';
        $lines[] = '<a href="http://route.web.de/">Wegbeschreibung für Ihre Homepage</a> mit dem kostenlosen WEB.DE Routenplaner.';
        $lines[] = '<a href="http://route.web.de/">Der kostenlose Routeplaner</a> von WEB.DE: Auch für Ihre Homepage!';
        $lines[] = '<a href="http://route.web.de/">Navigation für Ihre Homepage</a>: Der Routenplaner von WEB.DE.';

        if (count($lines) == 0)  //another fatal unexpeted error!
        {
          echo 'corrupted data file';
          die();
        }

        $randomIDX = mt_rand(0, count($lines) - 1);
        $text = trim($lines[$randomIDX]);
        add_option($optionName, $text);
      }
    }

    //Initialisierung der Länder Elemente
    function loadCountries()
    {
      $this->countries = array();
      $this->countries['---'] = 'Bitte w&auml;hlen...';
      $this->countries['ALB'] = 'Albanien';
      $this->countries['AND'] = 'Andorra';
      $this->countries['ARM'] = 'Armenien';
      $this->countries['AZE'] = 'Aserbaidschan';
      $this->countries['BEL'] = 'Belgien';
      $this->countries['BIH'] = 'Bosnien-Herzegowina';
      $this->countries['BGR'] = 'Bulgarien';
      $this->countries['DNK'] = 'D&auml;nemark';
      $this->countries['DEU'] = 'Deutschland';
      $this->countries['EST'] = 'Estland';
      $this->countries['FRO'] = 'F&auml;r&ouml;er Inseln';
      $this->countries['FIN'] = 'Finnland';
      $this->countries['FRA'] = 'Frankreich';
      $this->countries['GEO'] = 'Georgien';
      $this->countries['GIB'] = 'Gibraltar';
      $this->countries['GRC'] = 'Griechenland';
      $this->countries['GBR'] = 'Großbritannien';
      $this->countries['IRL'] = 'Irland';
      $this->countries['ISL'] = 'Island';
      $this->countries['ITA'] = 'Italien';
      $this->countries['HRV'] = 'Kroatien';
      $this->countries['LVA'] = 'Lettland';
      $this->countries['LIE'] = 'Liechtenstein';
      $this->countries['LTU'] = 'Litauen';
      $this->countries['LUX'] = 'Luxemburg';
      $this->countries['MLT'] = 'Malta';
      $this->countries['MKD'] = 'Makedonien';
      $this->countries['MDA'] = 'Moldawien';
      $this->countries['MCO'] = 'Monaco';
      $this->countries['NLD'] = 'Niederlande';
      $this->countries['NOR'] = 'Norwegen';
      $this->countries['AUT'] = '&ouml;sterreich';
      $this->countries['POL'] = 'Polen';
      $this->countries['PRT'] = 'Portugal';
      $this->countries['ROM'] = 'Rum&auml;nien';
      $this->countries['RUS'] = 'Russland';
      $this->countries['SMR'] = 'San Marino';
      $this->countries['SWE'] = 'Schweden';
      $this->countries['CHE'] = 'Schweiz';
      $this->countries['SCG'] = 'Serbien und Montenegro';
      $this->countries['SVK'] = 'Slowakische Republik';
      $this->countries['SVN'] = 'Slowenien';
      $this->countries['ESP'] = 'Spanien';
      $this->countries['CZE'] = 'Tschechische Republik';
      $this->countries['TUR'] = 'T&uuml;rkei';
      $this->countries['UKR'] = 'Ukraine';
      $this->countries['HUN'] = 'Ungarn';
      $this->countries['VAT'] = 'Vatikanstadt';
      $this->countries['BLR'] = 'Weißrussland';
      $this->countries['CYP'] = 'Zypern';
    }

    //Setzen der benötigten Hook
    function addActions()
    {
      include(dirname(__FILE__).'/wp_route_planner_web_de_widget.php');
      add_action('widgets_init', create_function( '', 'register_widget( "WPRoutePlannerWebDeWidget" );' ) );

      add_action('admin_menu', array($this, 'admin_menu'));

      add_action('init', array($this, 'tinymce_button'));
      add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

      add_shortcode('route_planner', array($this, 'shortcode_route_planner'));
    }

    //Verlinkung im Wordpress Adminmenü erstellen
    function admin_menu()
    {
      add_options_page('WP Routenplaner', 'WP Routenplaner', 'manage_options', 'WPRoutePlannerWebDe-settings-page', array($this, 'settings_page'));
    }

    //Ausgabe der Einstellungsseite
    function settings_page()
    {
      //Wenn der Parameter help exisitert, wird die Hilfeseite der Ländercodes ausgegeben
      if (isset($_GET['help']))
      {
        $content = '';
        echo '<div class="wrap">';
        echo '<h2>WP Routenplaner - L&auml;ndercodes</h2>';
        echo '<table>';
        echo '<tr>';
        echo '<td>Land</td>';
        echo '<td>Code</td>';
        echo '</tr>';
        foreach ($this->countries as $code => $countryName)
        {
          if ($code == '---')
            continue;

          echo '<tr>';
          echo '<td>'.$countryName.'</td>';
          echo '<td>'.$code.'</td>';
          echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
        return $content;
      }

      $save_notice = '';
      //Speicherung der Eingabefelder
      if (isset($_POST['save-settings']))
      {
        update_option('WPRoutePlannerWebDe-to_street', $_POST['to_street']);
        update_option('WPRoutePlannerWebDe-to_plz', $_POST['to_plz']);
        update_option('WPRoutePlannerWebDe-to_city', $_POST['to_city']);
        update_option('WPRoutePlannerWebDe-to_country', $_POST['to_country']);
        update_option('WPRoutePlannerWebDe-use_destination', $_POST['use_destination']);

        $save_notice = '<div class="updated">
             <p>Die Einstellungen wurden erfolgreich gespeichert.</p>
         </div>';

      }

      //Laden der Optionswerte
      $to_street = get_option('WPRoutePlannerWebDe-to_street');
      $to_plz = get_option('WPRoutePlannerWebDe-to_plz');
      $to_city = get_option('WPRoutePlannerWebDe-to_city');
      $to_country = get_option('WPRoutePlannerWebDe-to_country');
      $use_destination = get_option('WPRoutePlannerWebDe-use_destination') == 1 ? ' checked="checked"' : '';

      $toCountrySelect = $this->generateCountrySelect('to_country', 4, $to_country);

      $content =
<<<EOF
  <div class="wrap">
    $save_notice
    <h2>WP Routenplaner - Einstellungen</h2>
    <div>
      <p>
        Auf dieser Seite haben Sie die Möglichkeit, die Einstellungen für das Plugin WP Routenplaner
        vorzunehmen. Weiterhin finden Sie hier Hilfe zur Verwendung der Shortcodes.
      </p>
      <h3>Feste Zieladresse festlegen</h3>
      <p>
        Wenn Sie eine feste Zieladresse verwenden möchten, aktivieren Sie bitte die folgende Checkbox und
        tragen Sie die gewünschte Zieladresse ein:
      </p>
      <p>
        <form method="post" action="">
          <input type="checkbox" id="use_destination" name="use_destination" value="1" style="margin-right: 5px;"$use_destination><label for="use_destination">feste Zieladresse verwenden</label><br/><br/>
          <table class="form-table">
            <tr valign="top">
              <th scope="row">Straße / Hausnummer</th>
              <td><input type="text" name="to_street" value="$to_street" /></td>
            </tr>

            <tr valign="top">
              <th scope="row">PLZ</th>
              <td><input type="text" name="to_plz" value="$to_plz" /></td>
            </tr>

            <tr valign="top">
              <th scope="row">Ort</th>
              <td><input type="text" name="to_city" value="$to_city" /></td>
            </tr>

            <tr valign="top">
              <th scope="row">Land</th>
              <td>$toCountrySelect</td>
            </tr>

          </table>

          <p class="submit">
          <input type="submit" class="button-primary" name="save-settings" value="Voreinstellungen speichern" />
          </p>
        </form>
      </p>
      <h3>Hilfe zur Verwendung des Shortcodes</h3>
      <p>
        Mit Hilfe des Shortcodes [route_planner] haben Sie die Möglichkeit, unabhängig von einer evtl. oben
        eingetragenen Zieladresse direkt beim Erstellen feste Zieladressen zu hinterlegen. Diesen Shortcode
        fügen Sie an die gewünschte Stelle in der Page / im Post ein.
      </p>
      <p>
        Folgende Attribute stehen für den Shortcode zur Verfügung:
        <ul style="list-style-type:disc;list-style-position:inside">
          <li>to_street</li>
          <li>to_plz</li>
          <li>to_city</li>
          <li>to_country (in Form des Ländercodes, z.B. DEU für Deutschland) <span title="Albanien = ALB\nAndorra = AND\nArmenien = ARM\nAserbaidschan = AZE\nBelgien = BEL\nBosnien-Herzegowina = BIH\nBulgarien = BGR\nD&auml;nemark = DNK\nDeutschland = DEU\nEstland = EST\nF&auml;r&ouml;er Inseln = FRO\nFinnland = FIN\nFrankreich = FRA\nGeorgien = GEO\nGibraltar = GIB\nGriechenland = GRC\nGroßbritannien = GBR\nIrland = IRL\nIsland = ISL\nItalien = ITA\nKroatien = HRV\nLettland = LVA\nLiechtenstein = LIE\nLitauen = LTU\nLuxemburg = LUX\nMalta = MLT\nMakedonien = MKD\nMoldawien = MDA\nMonaco = MCO\nNiederlande = NLD\nNorwegen = NOR\n&ouml;sterreich = AUT\nPolen = POL\nPortugal = PRT\nRum&auml;nien = ROM\nRussland = RUS\nSan Marino = SMR\nSchweden = SWE\nSchweiz = CHE\nSerbien und Montenegro = SCG\nSlowakische Republik = SVK\nSlowenien = SVN\nSpanien = ESP\nTschechische Republik = CZE\nT&uuml;rkei = TUR\nUkraine = UKR\nUngarn = HUN\nVatikanstadt = VAT\nWeißrussland = BLR\nZypern = CYP\n" style="font-weight:bold;"><a href="./options-general.php?page=WPRoutePlannerWebDe-settings-page&help" target="_blank">Hilfe?</a></span></li>
        </ul>
        <span style="text-decoration:underline;">Beispiel:</span><br/>
        <br/>
        Der Shortcode<br/>
        <br/>
        <code>
          [route_planner to_street="Musterstraße 23" to_plz="12345" to_city="Musterstadt" to_country="DEU"]
        </code><br/><br/>
        würde folgende Zieladresse ausgeben: <br/>
        <br/>
        Musterstraße 23<br/>
        12345 Musterstadt<br/>
        Deutschland<br/>
      </p>

      <h3>Hinweis zur Verwendung</h3>
      <p>
        Ist der Routenplaner eingebunden, hat der Besucher Ihrer Webseite die Möglichkeit, die
        entsprechenden Daten zur Routenberechnung einzutragen. Beim Klick auf „Route berechnen“ öffnet
        sich ein neuer Tab mit dem <a href="http://route.web.de" target="_blank">Web.de Routenplaner</a>, die eingetragenen Daten werden übernommen.
        Nun kann der Besucher seinen favorisierten Anbieter zur Routenberechnung wählen.
      </p>
    </div>



  </div>
EOF;

      echo $content;
    }

    //CSS Style im Header laden
    function enqueue_scripts()
    {
      wp_register_style( 'WPRoutePlannerWebDe-style', plugins_url('css/style.css', __FILE__) );
      wp_enqueue_style( 'WPRoutePlannerWebDe-style' );
    }

    //Button für den grafischen Editor hinzufügen
    function tinymce_button()
    {

      if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
      {
        return;
      }

      if ( get_user_option('rich_editing') == 'true' )
      {
        add_filter( 'mce_external_plugins', array($this, 'tinymce_button_add_plugin'));
        add_filter( 'mce_buttons', array($this, 'tinymce_button_register'));
      }

    }

    function tinymce_button_register($buttons)
    {
      array_push( $buttons, "|", "route_planner" );
      return $buttons;
    }

    function tinymce_button_add_plugin( $plugin_array )
    {
       $plugin_array['route_planner'] = plugins_url( 'js/route_planner.js' , __FILE__ );
       return $plugin_array;
    }

    //Shortode Hook zur Anzeige des Routenplaners
    function shortcode_route_planner($atts)
    {
      $content = '';

      $content .= $this->display_route_planner($atts);

      return $content;
    }

    //Ausgabe des Routenplaner Formulars
    function display_route_planner($atts)
    {
      //Initialisierung der Variablen
      extract(shortcode_atts(
        array(
	      'from_street' => NULL,
        'from_plz' => NULL,
        'from_city' => NULL,
        'from_country' => NULL,
        'to_street' => NULL,
        'to_plz' => NULL,
        'to_city' => NULL,
        'to_country' => NULL,
        'width' => -1
        ), $atts));

      //Nur wenn das häkchen in der Optionen gesetzt wird, werden die globalen Werte geladen
      $use_destination =  get_option('WPRoutePlannerWebDe-use_destination') == '1';

      if ($use_destination)
      {
        if (!isset($to_street))
          $to_street = get_option('WPRoutePlannerWebDe-to_street');

        if (!isset($to_plz))
          $to_plz = get_option('WPRoutePlannerWebDe-to_plz');

        if (!isset($to_city))
          $to_city = get_option('WPRoutePlannerWebDe-to_city');

        if (!isset($to_country))
          $to_country = get_option('WPRoutePlannerWebDe-to_country');
      }

      $tabCounter = 5;

      //Prüfung ob die Eingabemaske oder ein vordefiniertes Feld angezeigt werden sollen
      $contentToStreet = '';
      if (isset($to_street) && strlen(trim($to_street)) > 0)
      {
        $contentToStreet = '<span id="tostreet_value">'.$to_street.'</span>';
        $contentToStreet .= '<input type="hidden" name="tostreet" value="'.$to_street.'"/>';
      }
      else
      {
        $contentToStreet = '<input id="tostreet" name="tostreet" value="Adresse" tabindex="'.$tabCounter.'" onfocus="if (this.value == \'Adresse\') this.value = \'\';" type="text"/>';
        $tabCounter++;
      }

      $contentToPLZ = '';
      if (isset($to_plz) && strlen(trim($to_plz)) > 0)
      {
        $contentToPLZ = '<span id="toplz_value">'.$to_plz.'</span>';
        $contentToPLZ .= '<input type="hidden" name="toplz" value="'.$to_plz.'"/>';
      }
      else
      {
        $contentToPLZ = '<input id="toplz" name="toplz" size="2" value="PLZ" maxlength="10" tabindex="'.$tabCounter.'" onfocus="if (this.value == \'PLZ\') this.value = \'\';" type="text"/>';
        $tabCounter++;
      }

      $contentToCity = '';
      if (isset($to_city) && strlen(trim($to_city)) > 0)
      {
        $contentToCity = '<span id="tocity_value">'.$to_city.'</span>';
        $contentToCity .= '<input type="hidden" name="tocity" value="'.$to_city.'"/>';
      }
      else
      {
        $contentToCity = '<input id="tocity" name="tocity" size="8" value="Ort" tabindex="'.$tabCounter.'" onfocus="if (this.value == \'Ort\') this.value = \'\';" type="text"/>';
        $tabCounter++;
      }

      $countrySelectorFrom = $this->generateCountrySelect('fromcountry', 4, isset($from_country) ? $from_country : 'DEU');

      $countrySelectorTo = '';
      if (isset($to_country) && strlen(trim($to_country)) > 0 && $to_country != '---')
      {
        $countrySelectorTo = '<span id="tocountry_value">'.(isset($this->countries[$to_country]) ? $this->countries[$to_country] : 'Land unbekannt').'</span>';
        $countrySelectorTo .= '<input type="hidden" name="tocountry" value="'.$to_country.'"/>';
      }
      else
        $countrySelectorTo = $this->generateCountrySelect('tocountry', $tabCounter, (isset($to_country) ? $to_country : 'DEU'));

      $planner_style = '';
      if ($width > 0)
        $planner_style = ' style="width: '.$width.'px"';


      $optionName = 'wp_route_planner_web_de_text';
      $text = get_option($optionName);

      if ($text === false)
      {
        $content = 'Fehler beim Anzeigen des Formulars';
        return $content;
      }

      $content =
<<<EOF
  <div class="route_planner"$planner_style>
    <form method="post" action="http://route.web.de/" style="margin: 0 0 0 10px;" target="_blank">
      <div class="from">
        <h3 id="fromtitle">Start</h3>
        <input id="fromstreet" name="fromstreet" value="Straße" tabindex="1" onfocus="if (this.value == 'Straße') this.value = '';" type="text"/>
        <br/>
        <input id="fromplz" name="fromplz" value="PLZ" size="2" maxlength="10" tabindex="2" onfocus="if (this.value == 'PLZ') this.value = '';" type="text"/>
        <input id="fromcity" name="fromcity" value="Ort" size="8" tabindex="3" onfocus="if (this.value == 'Ort') this.value = '';" type="text"/>
        <br/>
        $countrySelectorFrom
      </div>
      <div class="to">
        <h3 id="totitle">Ziel</h3>
        $contentToStreet
        <br/>
        $contentToPLZ
        $contentToCity
        <br/>
        $countrySelectorTo
      </div>
      <div class="route_planner_button">
        <input id="planer" type="hidden" value=""/>
        <input type="hidden" name="source" value="wordpress"/>
        <input type="submit" value="Route berechnen" style="margin: 5px 0; font-size: 11px !important;"/>
      </div>
      <div class="route_planner_text">
        $text
      </div>
    </form>
  </div>
EOF;


      return $content;
    }

    //Wrapper zum Laden der Landauswahl
    function generateCountrySelect($selectName, $tabindex, $selectedCountry = NULL)
    {
      $content = '';

      $content .= '<select id="'.$selectName.'" name="'.$selectName.'" tabindex="'.$tabindex.'" style="margin: 5px 5px 0 0px;">';
      foreach ($this->countries as $countryCode => $countryName)
      {
        if ($selectedCountry == $countryCode)
          $content .= '<option value="'.$countryCode.'" selected="selected">'.$countryName.'</option>';
        else
          $content .= '<option value="'.$countryCode.'">'.$countryName.'</option>';
      }

      $content .= '</select>';

      return $content;
    }
  }
}

//Initialisierung der Klasse
global $WPRoutePlannerWebDe;
$WPRoutePlannerWebDe = NULL;
if (class_exists("WPRoutePlannerWebDe"))
{
  $WPRoutePlannerWebDe = new WPRoutePlannerWebDe();
}
?>
