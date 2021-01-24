
# php ISDOC Writer (neoficiální)
Cílem je jednoduchá PHP třída umožňující export účetního dokladu do formátu ISDOC.

## Co je ISDOC
ISDOC je univerzální formát pro elektronickou výměnu faktur a účetních dokumentů.  
ISDOC je formát elektronické fakturace, který umožňuje bezpapírovou výměnu faktur a dalších dokladů, jejich rychlé zpracování a přenositelnost.

Více o formátu:
* Wikipedia: https://cs.wikipedia.org/wiki/ISDOC
* Oficiální web: http://www.isdoc.org/

### Specifikace formátu
* http://www.isdoc.cz/6.0/doc/isdoc.html
* http://www.isdoc.cz/6.0/readme-cs.html

### Schéma formátu
* http://isdoc.cz/6.0.1/doc-cs/isdoc-invoice-6.0.1.html

## Instalace
Zatím je zpracována minimalistická verze, umožňující export faktury do souboru ISDOC, který bez chybového hlášení otevře aplikace ISDOC reader.

Stačí spustit PHP soubor
* example/invoice.php  
nebo  
* example/multiple.php  
(pro export více účetních dokumentů do ZIP archivu rozčleněného podle typ dokladu do adresářů)

### ISDOC reader aplikace pro Windows
* http://www.isdoc.org/isdocreader/download/cz
