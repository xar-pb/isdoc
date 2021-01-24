# php ISDOC Writer (unofficial)
The goal is a simple PHP class that allows you to export an accounting document to ISDOC format.

## What is ISDOC
ISDOC is a universal format for the electronic exchange of invoices and accounting documents.  
ISDOC is an electronic invoicing format in the Czech Republic, which allows paperless exchanging of invoices and other documents, their quick processing and portability.

More about the ISDOC format:
* Wikipedia: https://cs.wikipedia.org/wiki/ISDOC
* Oficiální web: http://www.isdoc.org/

### Format specification
* http://www.isdoc.cz/6.0/doc/isdoc.html
* http://www.isdoc.cz/6.0/readme-en.html

### Format schema
* http://isdoc.cz/6.0.1/doc-en/isdoc-invoice-6.0.1.html

## Installation
So far, a minimalist version has been developed, allowing you to try exporting an invoice to an ISDOC file, which will be opened by the ISDOC reader application without an error message.

Just run the PHP file
* example/invoice.php  
or
* example/multiple.php  
(for export of multiple accounting documents to a ZIP archive divided into directories according to the document type)

### ISDOC reader application for Windows
* http://www.isdoc.org/isdocreader/download/en
