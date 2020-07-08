# Vizualizacija prirodnog kretanja stanovništva u Hrvatskoj

[![MIT license](https://img.shields.io/badge/License-MIT-blue.svg)](https://lbesson.mit-license.org/)
[![Generic badge](https://img.shields.io/badge/version-master-<COLOR>.svg)](https://shields.io/)

Projekt iz kolegija Vizualizacija podataka koji koristi skup podataka sa stranice http://data.gov.hr/ vezan za migracije unutar i izvan Hrvatske.
Obuhvaća kartu Hrvatske sa većim gradovima i općinama koje su definirane prema json datoteci sa lat i long geografskim vrijednostima.
Sadrži glavne informacije o pojedinom gradu te županiji poput broja E-građana u nekoj županiji te ukupnog broja doseljenih i odseljenih ljudi.
Moguće je i pokrenuti animaciju kojom se prikazuje promjena broja ljudi koji su migrirali od 2010. do 2016. godine ovisno o tipu migracije.

![Homepage](https://user-images.githubusercontent.com/52075105/86969197-b3c74b00-c16d-11ea-8123-cdc5bcb2ef2f.png)

Druga stranica prikazuje glavne informacije o županiji u obliku linearnog grafa.
Uz to omogućava izmjenu tipa migracije i prostora koji se promatra.

### Obuhvaćena su 4 prostora kretanja:
1. Grad/Općina
2. Županija
3. Inozemstvo
4. Ukupno

Linearni graf prikazuje prostor kretanja u ovisnosti o godini koja se promatra.
Manje vrijednosti za pojedinu godinu su bliže plavoj boji dok su veće vrijednosti bliže crvenoj.
Graf je moguće približiti, a dodatna pomoć je i dodatni graf koji se nalazi ispod glavnog.
On omogućava točniji odabir koji dio grafa se želi pobliže promotriti.
S desne strane grafa se nalaze metodološka objašnjenja glavnih stavki projekta koje omogućavaju bolje razumijevanje grafova.

![Secondpage](https://user-images.githubusercontent.com/52075105/86971109-ddce3c80-c170-11ea-8b3d-0b5ae147f933.png)

### Skidanje projekta i pokretanje
Projekt se jednostavno skida odabirom Clone gdje se kopira link github projekta te pomoću GitBasha ili drugog programa skida na lokalno računalo.
Drugi način je skidanje čitavog zipa
Projektne datoteke su tipa php te ih je moguće otvoriti pomoću Xamppa ili sličnog programa.
