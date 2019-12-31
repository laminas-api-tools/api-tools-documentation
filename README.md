Laminas API Tools Documentation Models
==============================

[![Build Status](https://travis-ci.org/laminas-api-tools/api-tools-documentation.png)](https://travis-ci.org/laminas-api-tools/api-tools-documentation)
[![Coverage Status](https://coveralls.io/repos/laminas-api-tools/api-tools-documentation/badge.png?branch=master)](https://coveralls.io/r/laminas-api-tools/api-tools-documentation)

This Laminas module can be used with conjunction with Laminas API Tools in order to:

- provide an object model of all captured documentation information, including:
  - All APIs available
  - All Services available in each API
  - All Operations available in each API
  - All required/expected Accept and Content-Type request headers, and expected
    Content-Type response header, for each available API Service Operation.
  - All configured fields for each service
- provide a configurable MVC endpoint for returning documentation
  - documentation will be delivered in a serialized JSON structure by default
  - end-users may configure alternate/additional formats via content-negotiation
