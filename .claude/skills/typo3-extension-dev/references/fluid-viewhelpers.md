# Fluid ViewHelper Reference for TYPO3 v14

Complete reference for commonly used Fluid ViewHelpers in TYPO3 extensions and sitepackages.

## Namespaces

```html
<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
      xmlns:cb="http://typo3.org/ns/TYPO3/CMS/ContentBlocks/ViewHelpers"
      data-namespace-typo3-fluid="true">
```

Custom extension ViewHelpers:
```html
xmlns:myext="http://typo3.org/ns/Vendor/MyExtension/ViewHelpers"
```

---

## Asset Management (preferred over TypoScript includeCSS/JS)

```html
<!-- CSS file -->
<f:asset.css identifier="main-css" href="EXT:my_ext/Resources/Public/Css/main.css" />
<f:asset.css identifier="main-css" href="EXT:my_ext/Resources/Public/Css/main.css" media="print" />

<!-- Inline CSS -->
<f:asset.css identifier="inline-styles">
    .my-class { color: red; }
</f:asset.css>

<!-- JS file -->
<f:asset.script identifier="main-js" src="EXT:my_ext/Resources/Public/JavaScript/main.js" />
<f:asset.script identifier="main-js" src="EXT:my_ext/Resources/Public/JavaScript/main.js" async="1" />
<f:asset.script identifier="main-js" src="EXT:my_ext/Resources/Public/JavaScript/main.js" defer="1" />

<!-- External CDN -->
<f:asset.css identifier="bootstrap" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" />
<f:asset.script identifier="bootstrap" src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" />

<!-- Inline JS -->
<f:asset.script identifier="config">
    const siteConfig = { baseUrl: '{f:uri.page(pageUid: site.rootPageId)}' };
</f:asset.script>

<!-- ES module -->
<f:asset.module identifier="my-module" src="EXT:my_ext/Resources/Public/JavaScript/module.js" />
```

Assets use unique `identifier` — adding the same identifier twice results in the last one winning.

---

## Links

```html
<!-- Page link -->
<f:link.page pageUid="42">Page Link</f:link.page>
<f:link.page pageUid="42" additionalParams="{foo: 'bar'}">With params</f:link.page>

<!-- Extbase action link -->
<f:link.action action="show" controller="Item" arguments="{item: item}">Show Item</f:link.action>
<f:link.action action="list" controller="Item" pageUid="42" class="btn">List</f:link.action>

<!-- Typolink (most flexible — handles all TYPO3 link types) -->
<f:link.typolink parameter="{item.link}">Link Text</f:link.typolink>
<f:link.typolink parameter="42" class="my-class" target="_blank">Page 42</f:link.typolink>
<f:link.typolink parameter="t3://page?uid=42&type=1">Typed link</f:link.typolink>

<!-- Email link -->
<f:link.email email="info@example.com" />

<!-- External link -->
<f:link.external uri="https://example.com" target="_blank" rel="noopener">External</f:link.external>

<!-- File link -->
<f:link.file file="{fileReference}" download="true">Download</f:link.file>
```

## URIs (URL only, no <a> tag)

```html
<f:uri.page pageUid="42" />
<f:uri.action action="show" arguments="{item: item}" absolute="1" />
<f:uri.image image="{fileReference}" maxWidth="300" />
<f:uri.resource path="EXT:my_ext/Resources/Public/Icons/logo.svg" />
<f:uri.typolink parameter="42" />
```

---

## Images

```html
<!-- From FAL file reference -->
<f:image image="{fileReference}" maxWidth="800" alt="{fileReference.alternative}" />
<f:image image="{fileReference}" width="300c" height="200c" />

<!-- From extension resource -->
<f:image src="EXT:my_ext/Resources/Public/Images/logo.svg" width="200" alt="Logo" />

<!-- Crop variants -->
<f:image image="{fileReference}" maxWidth="800" cropVariant="mobile" />

<!-- Additional attributes -->
<f:image image="{fileReference}" maxWidth="800" class="img-fluid" loading="lazy" />
```

Crop shorthand: `300c` = crop to 300px, `300m` = max 300px preserving ratio.

---

## Conditions

```html
<f:if condition="{items}">
    Content shown if items is truthy
</f:if>

<f:if condition="{items}">
    <f:then>Items exist</f:then>
    <f:else>No items</f:else>
</f:if>

<!-- Comparisons -->
<f:if condition="{item.status} == 'published'">Published</f:if>
<f:if condition="{items -> f:count()} > 5">More than 5</f:if>
<f:if condition="{item.featured}">Featured!</f:if>

<!-- Inline syntax -->
{f:if(condition: item.active, then: 'active', else: 'inactive')}
<div class="item {f:if(condition: iterator.isOdd, then: 'odd', else: 'even')}">
```

---

## Loops

```html
<f:for each="{items}" as="item" key="key" iteration="iter">
    Index: {iter.index}          <!-- 0-based -->
    Cycle: {iter.cycle}          <!-- 1-based -->
    Total: {iter.total}
    Is first: {iter.isFirst}
    Is last: {iter.isLast}
    Is odd: {iter.isOdd}
    Is even: {iter.isEven}
</f:for>

<!-- Grouped -->
<f:groupedFor each="{items}" as="group" groupBy="category" groupKey="categoryName">
    <h2>{categoryName}</h2>
    <f:for each="{group}" as="item">{item.title}</f:for>
</f:groupedFor>
```

---

## Formatting

```html
<!-- HTML (parse RTE content) -->
<f:format.html>{record.bodytext}</f:format.html>

<!-- Raw (no escaping) -->
<f:format.raw>{htmlContent}</f:format.raw>

<!-- Date -->
<f:format.date date="{item.crdate}" format="d.m.Y H:i" />
<f:format.date date="now" format="Y" />

<!-- Number -->
<f:format.number decimals="2" decimalSeparator="," thousandsSeparator=".">{price}</f:format.number>

<!-- Currency -->
<f:format.currency currencySign="€" decimalSeparator="," thousandsSeparator="." prependCurrency="false">{price}</f:format.currency>

<!-- Crop -->
<f:format.crop maxCharacters="100" append="&hellip;">{text}</f:format.crop>

<!-- Strip tags -->
<f:format.stripTags>{htmlContent}</f:format.stripTags>

<!-- Bytes -->
<f:format.bytes>{fileSize}</f:format.bytes>

<!-- JSON -->
<f:format.json>{arrayData}</f:format.json>

<!-- HTML entities -->
<f:format.htmlentities>{userInput}</f:format.htmlentities>

<!-- nl2br -->
<f:format.nl2br>{plainText}</f:format.nl2br>

<!-- Trim -->
<f:format.trim>{text}</f:format.trim>

<!-- Case -->
<f:format.case mode="upper">{text}</f:format.case>
<!-- modes: upper, lower, capital, uncapital -->

<!-- Padding -->
<f:format.padding padLength="10" padString="0">{number}</f:format.padding>

<!-- Printf -->
<f:format.printf arguments="{0: item.title, 1: item.count}">%s has %d entries</f:format.printf>
```

---

## Translation

```html
<!-- By key (current extension) -->
<f:translate key="list.header" />

<!-- Full LLL path -->
<f:translate key="LLL:EXT:my_ext/Resources/Private/Language/locallang.xlf:list.header" />

<!-- With extension name -->
<f:translate key="list.header" extensionName="MyExtension" />

<!-- With arguments (placeholders) -->
<f:translate key="welcome.message" arguments="{0: user.name}" />

<!-- Default value -->
<f:translate key="maybe.missing" default="Fallback text" />

<!-- Inline -->
{f:translate(key: 'list.header')}
{f:translate(key: 'LLL:EXT:my_ext/Resources/Private/Language/locallang.xlf:key')}
```

---

## Rendering

```html
<!-- Render a section -->
<f:render section="Main" />
<f:render section="Main" arguments="{items: items}" />

<!-- Render a partial -->
<f:render partial="Navigation/Menu" arguments="{_all}" />
<f:render partial="Item" arguments="{item: item, settings: settings}" />

<!-- Optional rendering -->
<f:render partial="Optional" optional="true" />

<!-- CObject (render TypoScript) -->
<f:cObject typoscriptObjectPath="lib.myObject" />
<f:cObject typoscriptObjectPath="lib.myObject" data="{record}" />
<f:cObject typoscriptObjectPath="{record.mainType}" data="{record}" table="{record.mainType}" />
```

---

## Forms (Extbase)

```html
<f:form action="create" controller="Item" object="{item}" name="item">
    <f:form.textfield property="title" class="form-control" />
    <f:form.textarea property="description" rows="5" class="form-control" />
    <f:form.hidden property="pid" value="42" />
    <f:form.select property="status" options="{draft: 'Draft', published: 'Published'}" class="form-select" />
    <f:form.checkbox property="featured" value="1" />
    <f:form.radio property="type" value="a" /> Type A
    <f:form.radio property="type" value="b" /> Type B
    <f:form.upload property="image" />
    <f:form.countrySelect property="country" />
    <f:form.submit value="Save" class="btn btn-primary" />
</f:form>

<!-- Validation results -->
<f:form.validationResults for="item">
    <f:if condition="{validationResults.hasErrors}">
        <f:for each="{validationResults.flattenedErrors}" key="property" as="errors">
            <f:for each="{errors}" as="error">
                <p class="text-danger">{property}: {error.message}</p>
            </f:for>
        </f:for>
    </f:if>
</f:form.validationResults>
```

---

## Flash Messages

```html
<f:flashMessages />
<f:flashMessages queueIdentifier="myQueue" as="messages">
    <f:for each="{messages}" as="message">
        <div class="alert alert-{message.severity}">{message.message}</div>
    </f:for>
</f:flashMessages>
```

---

## Variables & Debug

```html
<!-- Set variable -->
<f:variable name="myVar" value="hello" />
<f:variable name="count" value="{items -> f:count()}" />

<!-- Debug -->
<f:debug>{_all}</f:debug>
<f:debug maxDepth="3">{item}</f:debug>

<!-- Conditional debug -->
<f:if condition="1"><f:debug>{_all}</f:debug></f:if>
```

---

## Misc

```html
<!-- Alias -->
<f:alias map="{shortName: longVariableName}">
    {shortName.property}
</f:alias>

<!-- Count -->
{items -> f:count()}
<f:count subject="{items}" />

<!-- First / Last -->
{items -> f:first()}
{items -> f:last()}

<!-- Switch/Case -->
<f:switch expression="{item.type}">
    <f:case value="text">Text type</f:case>
    <f:case value="image">Image type</f:case>
    <f:defaultCase>Default type</f:defaultCase>
</f:switch>

<!-- Cycle (alternating values) -->
<f:for each="{items}" as="item">
    <f:cycle values="{0: 'odd', 1: 'even'}" as="class">
        <div class="{class}">{item.title}</div>
    </f:cycle>
</f:for>

<!-- Security: check FE user role -->
<f:security.ifHasRole role="editor">Editor content</f:security.ifHasRole>
<f:security.ifAuthenticated>Logged-in content</f:security.ifAuthenticated>

<!-- Cache control -->
<f:cache.disable>Uncached content</f:cache.disable>
<f:cache.static>Always cached content</f:cache.static>

<!-- Feature flag -->
<f:feature name="myFeature">Feature is enabled</f:feature>

<!-- Page title -->
<f:page.title>Custom Page Title</f:page.title>
```

---

## ContentBlocks ViewHelpers

When using ContentBlocks:

```html
<!-- Asset path for ContentBlock resources -->
{cb:assetPath()}

<!-- Language path for ContentBlock labels -->
{cb:languagePath()}

<!-- Usage example -->
<f:asset.css identifier="my-cb-css" href="{cb:assetPath()}/frontend.css" />
<f:translate key="{cb:languagePath()}:my.label" />
```

---

## Inline Notation

All ViewHelpers can be used inline:

```html
<!-- Tag notation -->
<f:format.date date="{item.crdate}" format="d.m.Y" />

<!-- Inline notation -->
{item.crdate -> f:format.date(format: 'd.m.Y')}

<!-- Chaining -->
{item.title -> f:format.crop(maxCharacters: 50) -> f:format.htmlentities()}

<!-- Inline condition -->
{f:if(condition: '{item.featured}', then: 'featured', else: '')}
```
