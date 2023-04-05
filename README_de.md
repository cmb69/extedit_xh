# Extedit_XH

Extedit_XH ermöglicht es, eine beliebige Anzahl so genannter „Extedits“ zu haben,
d.h. Inhaltsbereiche, die von Benutzern bearbeitet werden können, die mit
[Memberpages_XH](https://github.com/cmsimple-xh/memberpages) oder
[Register_XH](https://github.com/cmb69/register_xh) angemeldet sind.
Das erlaubt eine sehr beschränkte Mehrbenutzer-Fähigkeit,
ohne dass diesen Benutzern die volle Administrationsauthorisation
gewährt werden muss.
Das Plugin bietet prinzipiell die gleiche Funktionalität wie das
One Page for simpleMultiUser Plugin,
aber es verwendet den Editor von CMSimple_XH.
Aus Sicherheitsgründen wurde der Filebrowser durch einen minimalen Bildwähler
ersetzt.

- [Voraussetzungen](#voraussetzungen)
- [Download](#download)
- [Installation](#installation)
- [Einstellungen](#einstellungen)
- [Verwendung](#verwendung)
  - [Bildwähler](#bildwähler)
- [Einschränkungen](#einschränkungen)
- [Problembehebung](#problembehebung)
- [Lizenz](#lizenz)
- [Danksagung](#danksagung)

## Voraussetzungen

Extedit_XH ist ein Plugin für CMSimple_XH.
Es benötigt CMSimple_XH ≥ 1.7.0 und PHP ≥ 7.1.0.

## Download

Das [aktuelle Release](https://github.com/cmb69/extedit_xh/releases/latest)
kann von Github herunter geladen werden.

## Installation

The Installation erfolgt wie bei vielen anderen CMSimple_XH-Plugins auch.
Im [CMSimple_XH-Wiki](https://wiki.cmsimple-xh.org/de/?fuer-anwender/arbeiten-mit-dem-cms/plugins)
finden Sie weitere Informationen.

1. **Sichern Sie die Daten auf Ihrem Server.**
1. Entpacken Sie die ZIP-Datei auf Ihrem Rechner.
1. Laden Sie das ganze Verzeichnis `extedit/` auf Ihren Server in das `plugins/`
   Verzeichnis von CMSimple_XH hoch.
1. Machen Sie die Unterverzeichnisse `config/`, `css/` und `languages/`
   beschreibbar.
1. Navigieren Sie zu `Plugins` → `Extedit` im Administrationsbereich,
   um zu prüfen, ob alle Voraussetzungen erfüllt sind.

## Einstellungen

Die Plugin-Konfiguration erfolgt wie bei vielen anderen CMSimple_XH-Plugins
auch im Administrationsbereich der Website. Wählen Sie `Plugins` → `Extedit`.

Sie können die Voreinstellungen von Extedit_XH unter `Konfiguration` ändern.
Hinweise zu den Optionen werden beim Überfahren der Hilfe-Icons mit der Maus
angezeigt.

Die Lokalisierung wird unter `Sprache` vorgenommen. Sie können die
Sprachtexte in Ihre eigene Sprache übersetzen, falls keine entsprechende
Sprachdatei zur Verfügung steht, oder diese Ihren Wünschen gemäß anpassen.

<!-- TODO: customization of the editor -->

## Verwendung

Um ein „Extedit“ auf einer Seite einzubinden, fügen Sie folgendes ein:

    {{{extedit('%BENUTZERNAME%', '%TEXTNAME%');}}}

Um ein „Extedit“ im Template einzubinden, fügen Sie folgendes ein:

    <?php echo extedit('%BENUTZERNAME%', '%TEXTNAME%')?>

Hinweis: werden „Extedits“ im Template oder einer Newsbox eingebunden, muss
die Konfigurationsoption `Allow` → `Template` aktiviert werden.

Die Parameter haben die folgende Bedeutung:

- `%BENUTZERNAME%`:
  Der Name des Register_XH oder Memberpages_XH Benutzers, der den Inhalt
  bearbeiten darf. Wird `*` als Benutzername angegeben, dann dürfen *alle*
  authentifizierten Benutzer den Inhalt bearbeiten. Alternativ können auch mehrere
  Benutzernamen durch Kommata getrennt (ohne Leerzeichen) angegeben werden.

- `%TEXTNAME%`:
  Der eindeutige Name des „Extedits“. Lassen Sie diesen Parameter aus, um
  die Überschrift der enthaltenden Seite zu verwenden. Dieser Parameter muss
  angegeben werden, wenn der `extedit()` Aufruf im Template oder einer Newsbox
  erfolgt.

Wenn authorisierte Benutzer angemeldet sind, dann wird ihnen ein
`Bearbeiten` Link angezeigt, über den sie den Inhalt des „Extedits“ bearbeiten
können.
Sie können Bilder mit dem Editor einfügen, aber sie haben keinen Zugriff auf den
Filebrowser – nur ein einfacher [Bildwähler](#bildwähler) ist verfügbar.
Im Vorschaumodus kann der Administrator der Website ebenfalls das „Extedit“
bearbeiten, und hat dabei wie üblich Zugriff auf den Filebrowser.
Besucher sehen nur den Inhalt des „Extedits“.

Es ist möglich eine beliebige Anzahl von „Extedits“ auf einer einzelnen Seite
darzustellen, wobei alle diese einem bestimmten Benutzer zugeordnet sind, jedes
einem anderen Benutzer zugeordnet ist, oder eine Mischung davon. Um zu
verhindern, dass ein Benutzer versehentlich Änderungen eines anderen Benutzers
überschreibt, wurde eine einfache optimistische Sperre implementiert.

Die Inhalte aller „Extedits“ werden im automatisch erstellten Unterordner
`extedit/` des aktuellen `content/` Ordners gespeichert,
jeder in einer eigenen Datei.
Der Dateiname besteht aus dem `%TEXTNAME%`n aus dem alle ungültigen Zeichen
entfernt wurden (nur alphanumerische Zeichen und Minuszeichen sind erlaubt).
*Daher müssen alle entsprechend behandelnden `%TEXTNAME%`n
für jede Sprache der CMSimple_XH Installation eindeutig sein.*

Vorsicht: wenn Sie den `%TEXTNAME%` Parameter auslassen, dann wird statt dessen
die Seitenüberschrift verwendet. Wenn Sie diese später ändern, muss die
„Extedit“-Datei manuell umbenannt werden. Weiterhin ist es absolut möglich die
selbe Seitenüberschrift mehrfach in unterschiedlichen Zweigen des TOC unter
CMSimple_XH zu verwenden, aber das funktioniert nicht mit Extedit_XH. Daher ist
es wahrscheinlich besser, wenn Sie den `%TEXTNAME%` Parameter immer explizit
angeben.

Es ist möglich Pluginaufrufe in den „Extedits“ zu verwenden (was in der
Konfiguration aktiviert werden muss), aber dies ist nur bedingt sinnvoll, da die
Benutzer die Plugins nicht verwalten können. Allerdings benötigen manche Plugins
keine Administration, so dass sie ad-hoc verwendet werden können, und andere
Plugins können vom Administrator vorbereitet werden.

### Bildwähler

Da der Dateibrowser aus Sicherheitsgründen nur für den Administrator
der Website verfügbar ist, bietet Extedit_XH einen simplistischen Bildwähler für
Benutzer, die über Memberpages_XH oder Register_XH angemeldet sind.

Per Voreinstellung hat der Benutzer nur Zugriff auf seinen eigenen
Unterordner des Bilderordners (normalerweise `userfiles/images/`). Dieser
Unterordner muss den Namen des Benutzers haben, und er muss vom Administrator
angelegt werden. Der Benutzer kann Bilder in diesen Ordner hoch laden,
aber diese nicht löschen oder umbennen.
Weiterhin kann er nicht auf Unterordner seines Bilderordners zugreifen.

## Einschränkungen

- Der Bildwähler funktioniert derzeit nur unter TinyMCE4 und CKEditor.
- Fortgeschrittene Verwaltung der „Extedit“ Dateien (z.B. Löschen und Umbenennnen) muss
  per FTP vorgenommen werden.

## Problembehebung

Melden Sie Programmfehler und stellen Sie Supportanfragen entweder auf
[Github](https://github.com/cmb69/extedit_xh/issues)
oder im [CMSimple_XH Forum](https://cmsimpleforum.com/).

## Lizenz

Extedit_XH ist freie Software. Sie können es unter den Bedingungen
der GNU General Public License, wie von der Free Software Foundation
veröffentlicht, weitergeben und/oder modifizieren, entweder gemäß
Version 3 der Lizenz oder (nach Ihrer Option) jeder späteren Version.

Die Veröffentlichung von Extedit_XH erfolgt in der Hoffnung, dass es
Ihnen von Nutzen sein wird, aber *ohne irgendeine Garantie*, sogar ohne
die implizite Garantie der *Marktreife* oder der *Verwendbarkeit für einen
bestimmten Zweck*. Details finden Sie in der GNU General Public License.

Sie sollten ein Exemplar der GNU General Public License zusammen mit
Extedit_XH erhalten haben. Falls nicht, siehe <https://www.gnu.org/licenses/>.

© 2013-2023 Christoph M. Becker

Dänische Übersetzung © 2013 Jens Maegaard

## Danksagung

Das Pluginlogo wurde von [Alessandro Rei](http://www.mentalrey.it/) gestaltet.
Vielen Dank für die Veröffentlichung des Icons unter GPL.

Vielen Dank an die Gemeinde im [CMSimple_XH-Forum](http://www.cmsimpleforum.com/)
für Hinweise, Vorschläge und das Testen.
Besonders möchte ich *Ulrich*, *svasti* und *Hartmut* für das frühe Feedback danken.
Vielen Dank auch an *Ele*, der schnell einen kritischen Fehler im RC-Stadium gemeldet,
und bei der Fehlerbehebung geholfen hat.

Und zu guter letzt vielen Dank an [Peter Harteg](http://www.harteg.dk/),
den „Vater“ von CMSimple, und allen Entwicklern von
[CMSimple_XH](https://www.cmsimple-xh.org/de/) ohne die es dieses
phantastische CMS nicht gäbe.
