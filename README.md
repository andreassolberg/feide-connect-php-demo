
# Feide Connect Demo PHP klient


Dette er eksempelkode i PHP for uthenting av eksamensresultater fra FS via Feide Connect.

*Denne koden skal kun brukes for ekperimentering og opplæring.*

Vi anbefaler at du gjør følgende:

1. Installerer og setter opp denne PHP applikasjonen og får den til å fungere mot Feide Connect.
2. Tar en titt på koden

Du kan også 

* [Teste vår ferdig installerte og konfigurerte versjon av den samme appen](http://eksamen.andreas.uninettlabs.no/php/) for å sammenligne hvordan det skal fungere.


## Installer og konfigurer denne demo appen

Drop filene inn på en webtjener (helst Apache) med støtte for PHP.


**Angående versjon av PHP.** Demokoden anvender [JSON_PRETTY_PRINT](http://php.net/manual/en/function.json-encode.php) som ble innført i PHP 5.4.0. Dersom man benytter en eldre versjon av PHP kan man bare fjerne dette flagget der de benyttes i HTML malene.


Besøk forsiden, og du vil forhåpentligvis se noe slik:

![](http://clippings.erlang.no/ZZ24FD8151.jpg)


Da er tiden inne for å registrere klienten på Feide Connect. Følg lenken til [Feide Connect Developer dashboard](https://dev.uwap.uninettlabs.no), og velg *Register new client*.

Fyll inn navn og beskrivelse og lim inn Redirect URI-en du fikk opp tidligere...

![](http://clippings.erlang.no/ZZ6FFAC34F.jpg)


Finn eksamensresultater API og be om tilgang til dette.

![](http://clippings.erlang.no/ZZ3B2895F9.jpg)


Verifiser senere at du har blitt tildelt tilgang:

![](http://clippings.erlang.no/ZZ79F2F272.jpg)


Nå er det tid for å fylle ut informasjonen du har fått tildelt i developer dashboard inn i applikasjonens konfigurasjonsfil `etc/config.php`.


![](http://clippings.erlang.no/ZZ71FCD9CE.jpg)
![](http://clippings.erlang.no/ZZ51FA029B.jpg)



Etter å ha lagt inn konfigurasjonen kan du refreshe siden til appen, og du skal nå få opp:

![](http://clippings.erlang.no/ZZ552967BA.jpg)

Velg *login*.

Først får man valg om hvor man vil logg inn med. Dersom man ønsker å logge inn med Feide sin testbruker så velger man *Feide*.

Deretter logger man inn med testbrukernavn og passord (spør Andreas).

![](http://clippings.erlang.no/ZZ3EEF9417.jpg)

Man vil så som sluttbruker bekrefte at man ønsker å gi tilgang til at klienten henter ut eksamensresultater og brukerinformasjon:

![](http://clippings.erlang.no/ZZ181480E7.jpg)

Når appen klarer å hente ut eksamensresultater er man i mål! Gratulerer!

![](http://clippings.erlang.no/ZZ5A498725.jpg)






## Forstå koden


All kode av betydning og interesse ligger i `lib/FeideConnect.php`. Her finner du en komplett implementasjon av OAuth 2.0 consumer uten eksterne avhengigheter.

* [Studer kildekoden for lib/FeideConnect.php her](http://github.com/andreassolberg/.....)

Filen `index.php` styrer logikken for selve applikasjonen, og laster f.eks. inn konfigurasjon fra `etc/config.php`.

Alt under `templates` er HTML maler som benyttes for presentasjon av innholdet på web. Dette benytter seg av bootstrap rammeverket for responsivt design. Alt under `bower_components` er knyttet til [bootstrap](http://getbootstrap.com).




