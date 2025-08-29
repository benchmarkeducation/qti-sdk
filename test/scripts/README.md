# QTI 3.0 Test Scripts

## Prerequisites

Make sure you installed QTI SDK using [Composer](https://getcomposer.org/download/). See the main README.md file
at the root of the project for more information about installation.

## QTI 3.0 Comprehensive Test

Run the QTI 3.0 comprehensive test suite to validate the gold standard package:

```shell
php test-qti3-comprehensive.php
```

This script tests:
- **Manifest Loading** - IMS Content Package manifest validation
- **Assessment Test** - QTI 3.0 test structure and elements
- **Assessment Item** - QTI 3.0 item with inline choice interaction
- **Item Session** - Runtime session management and response processing

## Gold Standard Package

The test script validates the QTI 3.0 gold standard package located at `../xml-files/gold-standard/` which contains reference implementations of QTI 3.0 content.