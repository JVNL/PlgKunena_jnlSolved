# Plugin Kunena Joomla!NL Solved (v2.0.0)

Kunena-plugin waarmee de topicstarter (en admins/moderators) een topic als
"opgelost" kunnen markeren. Werkt met het `nts_kpraxis`-template (getest
tegen de daadwerkelijke markup van joomlanl.nl).

## Wat is er veranderd t.o.v. 1.0.3

### Bugfixes
- **CSRF-kwetsbaarheid verholpen.** `onBeforeRender()` voerde de solved/reopen-actie
  voorheen uit puur op basis van GET-parameters, zonder tokencontrole. Een
  gefabriceerde link kon een ingelogde gebruiker (of moderator) laten
  meewerken aan het sluiten/heropenen van een topic zonder dat diegene dat
  wilde. De front-end knoppen sturen nu `Session::getFormToken()` mee als
  GET-parameter en de server controleert dit met `Session::checkToken('get')`.
- **`ReopenTopic()`-bug gefixt.** `stripos($subject, $marker)` gaf `0` terug
  wanneer de markeringstekst (bijv. `[opgelost]`) vooraan de titel stond —
  en `0` is falsy in PHP. Daardoor faalde de heropen-actie voor precies het
  normale geval waarin een opgelost-maar-niet-vergrendeld topic heropend
  moest worden. Overal wordt nu consequent `!== false` gebruikt
  (`Auth::isMarkedSolved()`).
- **Fragiele regex/`str_replace`-injectie vervangen door echte DOM-manipulatie**
  (`src/Render.php`). De oude aanpak verving *elke* voorkomst van de
  gematchte string, dus bij twee identieke stukjes HTML werd de content
  gedupliceerd; en de ongreedy regex kon bij geneste `<div>`'s bij de
  verkeerde sluit-tag stoppen. Nu wordt de pagina als DOM geparsed en wordt
  de knop als échte sibling-node ingevoegd na elk element met de class
  `kmessagepadding` — functioneel identiek aan het origineel, maar
  structuur-bestendig.
- **Message-ID-validatie.** De `berid`-parameter (welke post de oplossing
  is) werd voorheen blind opgeslagen. Nu wordt gecontroleerd dat het bericht
  daadwerkelijk bij het betreffende topic hoort voordat het wordt
  weggeschreven.
- Config-defaults in de PHP kwamen niet overeen met de defaults in de XML
  (bijv. `enable_for_admin` stond in de PHP op `0`, in de XML op `1`).
  Gelijkgetrokken.
- De taalstring `PLG_KUNENA_JNLSOLVED_TOPIC_SOLVED_LABEL` bestond al in de
  taalbestanden maar was aan geen enkel XML-veld gekoppeld (dode string).
  Er is nu een echt configureerbaar veld `topic_solved_text` (standaard
  `[opgelost]`) — de markeringstekst hoeft dus niet langer hardcoded in de
  PHP te staan.

- **Knop stond op een eigen regel i.p.v. in de bestaande knoppenrij.** De
  eerste v2-opzet voegde de solved/heropen-knop toe als een nieuwe,
  op zichzelf staande `<div>` ná de bestaande toolbar — een blok-element,
  dus altijd een nieuwe regel, ook als er ruimte genoeg was. De knop wordt
  nu als extra `<li>` toegevoegd aan de bestaande `<ul>` met
  actie-knoppen (Antwoord/Citeer/Bewerken/…), zodat hij in dezelfde rij
  meeloopt. Zie `Render::appendIntoButtonListByClass()`.

- **Knop verscheen ook in de topic-brede knoppenbalk** (Antwoord onderwerp/
  Schrijf uit/Favoriet), die dezelfde class `kmessagepadding` gebruikt als
  de per-post toolbar. Dat exemplaar zou sowieso nooit goed werken, omdat
  daar geen `.kmessage`-omhulsel omheen zit en `jnl_solved.js` de post-ID
  via `closest('.kmessage')` bepaalt. `Render::appendIntoButtonListByClass()`
  beperkt de zoekopdracht nu tot `kmessagepadding`-elementen die zich
  binnen een `.kmessage` bevinden.

### Modernisering
- **Kunena's eigen Topic/Message API** (`KunenaTopicHelper`, `KunenaTopic::save()/lock()`,
  `KunenaMessageHelper`) in plaats van losse `UPDATE`/`SELECT`-statements.
  Dit gaat via Kunena's eigen cache-/categorie-tellers-laag, in plaats van
  die te omzeilen.
- **Query builder met `quoteName()`** voor de eigen `#__kunena_jnlsolved`-tabel
  (i.p.v. losse string-concatenatie), plus een index op `topicid`.
- **Namespaced classes** onder `JoomlaNL\Plugin\Kunena\JnlSolved` (`src/`),
  geregistreerd via het `<namespace>`-element in `jnlsolved.xml` (Joomla
  4/5-standaard) én defensief via `JLoader::registerNamespace()` in
  `jnlsolved.php` zelf, voor het geval de PSR-4-autoload-cache niet
  geregenereerd is. De plugin-entrypoint blijft bewust een klassieke
  `CMSPlugin`-klasse (`jnlsolved.php` in de root) — Kunena's eigen
  plugin-groep (`group="kunena"`) triggert via de legacy
  `PluginHelper`/`dispatcher`-mechaniek, niet via Joomla's core
  event-subscribers, dus dat is hier de juiste/veiligste keuze.
- **Echte taalbestanden voor front-end teksten** (`language/*/xx-XX.plg_kunena_jnlsolved.ini`).
  Knoppen, modal-teksten en de "opgelost"-melding komen nu via `Text::_()`
  i.p.v. hardcoded Nederlandse strings in HTML-bestanden.
- **Geen jQuery-afhankelijkheid meer** in `jnl_solved.js` — Joomla 4/5
  garandeert jQuery niet meer standaard op de front-end.
- **`update`-server wijst naar jullie eigen GitHub-repo**
  (`JVNL/PlgKunena_jnlSolved`) i.p.v. een persoonlijk domein. Zie
  `versioninfo.xml` (hoort in de root van de repo, niet in de plugin-zip)
  — pas bij elke release `<version>` en `<downloadurl>` aan.
- Tabel `#__kunena_jnlsolved` gebruikt nu `utf8mb4` i.p.v. `latin1`.
  `sql/updates/2.0.0.sql` voegt de ontbrekende index toe voor bestaande
  installaties bij het upgraden.

## Belangrijk om te weten voor je test

- De DOM-injectie zoekt op **class `kmessagepadding`** (bevestigd tegen de
  echte markup van `nts_kpraxis`). Mocht het template ooit wijzigen en de
  knop niet meer verschijnen, is dat de eerste plek om te kijken
  (`Buttons::TOOLBAR_CLASS` in `src/Buttons.php`).
- De knop wordt na **elk** element met die class ingevoegd — dus onder elke
  post in het topic, niet alleen de eerste. Dat is bewust, identiek aan het
  origineel: elke post kan als oplossing worden aangewezen.
- `versioninfo.xml` moet je zelf op de juiste plek in de GitHub-repo zetten
  (root van de branch/tag waar de `<server>`-URL in `jnlsolved.xml` naar
  wijst) — die zit niet in de plugin-zip zelf.

## Installatie (lokaal testen)

1. Zip de map `jnlsolved/` (niet de bovenliggende map).
2. Installeer via Extensies → Beheren → Installeren, of vervang de bestaande
   plugin-map direct op de server en run daarna de update-database-stap
   (Systeem → Database) zodat `sql/updates/2.0.0.sql` wordt toegepast.
3. Controleer in Plugins → Kunena Joomla!NL Solved of de instellingen goed
   zijn overgenomen (met name de nieuwe "Markeringstekst"-instelling).
4. Test in elk geval: topic oplossen als topicstarter, heropenen als
   moderator, en dat de oude "opgelost"-links van vóór de update (zonder
   token) nu terecht *niets* meer doen totdat er via de knop (met token)
   wordt geklikt.

## Licentie

GNU General Public License version 3 or later; zie LICENSE.txt
