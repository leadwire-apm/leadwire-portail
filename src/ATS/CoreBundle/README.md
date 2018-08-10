### General
---
Core technical features

### Features
---
- ReST:

    - ✔️ Rest file upload endpoint. Generates a **FileUploadedEvent** on successful upload

- Commands:
    - ✔️ Base command

- Services:
    - ✔️ Generic Parser supporting CSV & JSON formats
    - ✔️ Generic Exporter based on Expresison Language
        - [] Add support for export formats other than CSV


### HOWTO:
---
Q: How to enable MongoDB full text Search ?

    - Simply annotate the desired document with:

    @ODM\@Index(keys={"username"="text"})

    N.B. The document manager provides textSearch()
