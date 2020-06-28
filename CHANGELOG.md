# Changelog

## [3.7.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/3.7.0) (2020-06-28)

**Merged pull requests:**

- Use symfony decorators [\#803](https://github.com/schmittjoh/JMSSerializerBundle/pull/803) ([goetas](https://github.com/goetas))

## [3.7.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/3.7.0) (2020-05-23)

**Implemented enhancements:**

- Add support for PHP 7.4 typed properties  [\#798](https://github.com/schmittjoh/JMSSerializerBundle/pull/798) ([goetas](https://github.com/goetas))

**Fixed bugs:**

- Missing dependency symfony/templating ? [\#799](https://github.com/schmittjoh/JMSSerializerBundle/issues/799)
- \[DefaultContext\] ContextFactoryInterface aliases not updated when using defaultContext.id config [\#781](https://github.com/schmittjoh/JMSSerializerBundle/issues/781)
- Conditional loading of templating and twig extensions [\#800](https://github.com/schmittjoh/JMSSerializerBundle/pull/800) ([goetas](https://github.com/goetas))

**Merged pull requests:**

- add .gitattributes [\#792](https://github.com/schmittjoh/JMSSerializerBundle/pull/792) ([Tobion](https://github.com/Tobion))
- \[doc\] Removed confusing JSON\_PRETTY\_PRINT suggestion from json\_deserialization [\#787](https://github.com/schmittjoh/JMSSerializerBundle/pull/787) ([wouterj](https://github.com/wouterj))
- Symfony 5 parameter doc fix [\#783](https://github.com/schmittjoh/JMSSerializerBundle/pull/783) ([michaljusiega](https://github.com/michaljusiega))
- \[defaultContext\] update ContextFactoryInterface aliases [\#782](https://github.com/schmittjoh/JMSSerializerBundle/pull/782) ([alexandre-abrioux](https://github.com/alexandre-abrioux))

## [3.5.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/3.5.0) (2019-11-29)

**Implemented enhancements:**

- Add ^5.0 in composer.json for symfony dependencies [\#777](https://github.com/schmittjoh/JMSSerializerBundle/issues/777) 

**Fixed bugs:**

- @VirtualProperty makes collection null [\#596](https://github.com/schmittjoh/JMSSerializerBundle/issues/596)

**Closed issues:**

- Jms deserializer class with parent properties [\#774](https://github.com/schmittjoh/JMSSerializerBundle/issues/774)
- Bad Serialization : object instead of array [\#773](https://github.com/schmittjoh/JMSSerializerBundle/issues/773)
- Interface 'Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface' not found [\#765](https://github.com/schmittjoh/JMSSerializerBundle/issues/765)
- JSON Serialization Options seem to be ignored [\#763](https://github.com/schmittjoh/JMSSerializerBundle/issues/763)
- Symfony. User Deprcated warning. [\#759](https://github.com/schmittjoh/JMSSerializerBundle/issues/759)

**Merged pull requests:**

- Fix typo in UPGRADING.md [\#760](https://github.com/schmittjoh/JMSSerializerBundle/pull/760) ([jdreesen](https://github.com/jdreesen))

## [3.4.1](https://github.com/schmittjoh/JMSSerializerBundle/tree/3.4.1) (2019-06-27)
**Fixed bugs:**

- Since 3.4.0, JSON\_PRESERVE\_ZERO\_FRACTION is no longer the default value `json\_encode` [\#755](https://github.com/schmittjoh/JMSSerializerBundle/issues/755)

**Merged pull requests:**

- Fix jms\_serializer.json\_serialization\_visitor default options [\#757](https://github.com/schmittjoh/JMSSerializerBundle/pull/757) ([fbourigault](https://github.com/fbourigault))


## [3.4.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/3.4.0) (2019-06-24)
**Implemented enhancements:**

- Xml deserialization options [\#744](https://github.com/schmittjoh/JMSSerializerBundle/pull/744) ([kopeckyales](https://github.com/kopeckyales))

**Merged pull requests:**

- Allow to call `JsonSerializationVisitorFactory::setOptions\(\)` with value `0` [\#749](https://github.com/schmittjoh/JMSSerializerBundle/pull/749) ([phansys](https://github.com/phansys))

## [3.3.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/3.3.0) (2019-04-23)
**Implemented enhancements:**

- Allow jms/serializer 3.0 [\#742](https://github.com/schmittjoh/JMSSerializerBundle/pull/742) ([goetas](https://github.com/goetas))

**Fixed bugs:**

- Visitor Configuration XML - Serialization references to Deserialization Configuration node [\#738](https://github.com/schmittjoh/JMSSerializerBundle/issues/738)

**Merged pull requests:**

- Add service aliases in order to enable autowiring for "configured\_serialization\_context\_factory" and "configured\_deserialization\_context\_factory" [\#741](https://github.com/schmittjoh/JMSSerializerBundle/pull/741) ([phansys](https://github.com/phansys))
- fixed XML Configuration - fixes schmittjoh/JMSSerializerBundle\#738 [\#740](https://github.com/schmittjoh/JMSSerializerBundle/pull/740) ([dennzo](https://github.com/dennzo))

## [2.4.4](https://github.com/schmittjoh/JMSSerializerBundle/tree/2.4.4) (2019-03-30)
**Merged pull requests:**

- Fix deprecation with Twig 2.7 \(2.x branch\) [\#736](https://github.com/schmittjoh/JMSSerializerBundle/pull/736) ([JustBlackBird](https://github.com/JustBlackBird))

## [3.2.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/3.2.0) (2019-03-21)
**Implemented enhancements:**

- Iterator handler [\#720](https://github.com/schmittjoh/JMSSerializerBundle/pull/720) ([simPod](https://github.com/simPod))

**Fixed bugs:**

- DoctrinePHPCRTypeDrive is missing r [\#724](https://github.com/schmittjoh/JMSSerializerBundle/issues/724)

**Closed issues:**

- Deprecated error with Twig 2.7.2 [\#731](https://github.com/schmittjoh/JMSSerializerBundle/issues/731)
- How to use "service" in Expose / Exclude [\#726](https://github.com/schmittjoh/JMSSerializerBundle/issues/726)
- Event Subscriber not being fired [\#725](https://github.com/schmittjoh/JMSSerializerBundle/issues/725)
- Expose / Exclude based on its ‘parent’ relationship in One to Many [\#723](https://github.com/schmittjoh/JMSSerializerBundle/issues/723)
- Exclude / Expose based on value of its relationship [\#722](https://github.com/schmittjoh/JMSSerializerBundle/issues/722)
- Deserializing don't work [\#721](https://github.com/schmittjoh/JMSSerializerBundle/issues/721)
- Unrecognized option "xml" under "jms\_serializer.visitors". Available options are xml, json [\#719](https://github.com/schmittjoh/JMSSerializerBundle/issues/719)
-  jms/serializer-bundle 3.1.0 requires jms/serializer ^2.0 conflict with minimum-stability [\#717](https://github.com/schmittjoh/JMSSerializerBundle/issues/717)

**Merged pull requests:**

- Fix deprecation with Twig 2.7 [\#733](https://github.com/schmittjoh/JMSSerializerBundle/pull/733) ([enumag](https://github.com/enumag))
- Fix typo in package name [\#732](https://github.com/schmittjoh/JMSSerializerBundle/pull/732) ([szepczynski](https://github.com/szepczynski))
- Document expression language providers [\#714](https://github.com/schmittjoh/JMSSerializerBundle/pull/714) ([goetas](https://github.com/goetas))

## [3.1.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/3.1.0)

**Implemented enhancements:**

- Allow to add expression function providers using DI tags [\#711](https://github.com/schmittjoh/JMSSerializerBundle/pull/711) ([goetas](https://github.com/goetas))

**Closed issues:**

- Symfony 4 - Service "jms\_serializer" not found [\#713](https://github.com/schmittjoh/JMSSerializerBundle/issues/713)
- Configuration property enable\_cache is missing [\#712](https://github.com/schmittjoh/JMSSerializerBundle/issues/712)
- IndexBy in OneToMany Annotation is ignored [\#710](https://github.com/schmittjoh/JMSSerializerBundle/issues/710)
- why there is backslash ? [\#709](https://github.com/schmittjoh/JMSSerializerBundle/issues/709)
- Declaration must be compatible [\#708](https://github.com/schmittjoh/JMSSerializerBundle/issues/708)
- Unrecognized option "xml" under "jms\_serializer.visitors" [\#698](https://github.com/schmittjoh/JMSSerializerBundle/issues/698)

## [3.0.1](https://github.com/schmittjoh/JMSSerializerBundle/tree/3.0.1) (2018-12-12)
**Fixed bugs:**

- EventDispatcher - use case sensitive event names; [\#695](https://github.com/schmittjoh/JMSSerializerBundle/pull/695) ([gam6itko](https://github.com/gam6itko))

**Closed issues:**

- Symfony 4.2 deprecation: A tree builder without a root node is deprecated [\#707](https://github.com/schmittjoh/JMSSerializerBundle/issues/707)
- DateTime / DateTime Deserialize without timezone [\#706](https://github.com/schmittjoh/JMSSerializerBundle/issues/706)
- Argument 1 passed to JMS\Serializer\Metadata\Driver\AbstractDoctrineTypeDriver::normalizeFieldType\(\) must be of the type string, null given [\#701](https://github.com/schmittjoh/JMSSerializerBundle/issues/701)

**Merged pull requests:**

- fix compatibility with Symfony Config 4.2 [\#705](https://github.com/schmittjoh/JMSSerializerBundle/pull/705) ([xabbuh](https://github.com/xabbuh))
- Update services.xml [\#702](https://github.com/schmittjoh/JMSSerializerBundle/pull/702) ([enumag](https://github.com/enumag))
- Xml keys were improperly named in config docs [\#696](https://github.com/schmittjoh/JMSSerializerBundle/pull/696) ([curiosity26](https://github.com/curiosity26))
- Update .travis.yml [\#693](https://github.com/schmittjoh/JMSSerializerBundle/pull/693) ([andreybolonin](https://github.com/andreybolonin))

## [3.0.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/3.0.0) (2018-11-09)
**Closed issues:**

- jms\_serializer directory in cache is not writable [\#640](https://github.com/schmittjoh/JMSSerializerBundle/issues/640)

**Merged pull requests:**

- setSerializeNull option is available only on the serialization context [\#694](https://github.com/schmittjoh/JMSSerializerBundle/pull/694) ([goetas](https://github.com/goetas))

## [3.0.0-RC2](https://github.com/schmittjoh/JMSSerializerBundle/tree/3.0.0-RC2) (2018-10-23)

**Fixed bugs:**

- Lazy services - Final classes cannot be proxied by ProxyManager [\#690](https://github.com/schmittjoh/JMSSerializerBundle/issues/690)

**Closed issues:**

- Provided class "JMS\Serializer\Handler\ArrayCollectionHandler" is final and cannot be proxied [\#692](https://github.com/schmittjoh/JMSSerializerBundle/issues/692)

**Merged pull requests:**

- remove lazy services, they are already lazy in many contexts [\#691](https://github.com/schmittjoh/JMSSerializerBundle/pull/691) ([goetas](https://github.com/goetas))

## [3.0.0-RC1](https://github.com/schmittjoh/JMSSerializerBundle/tree/3.0.0-RC1) (2018-10-17)

## [3.0.0-beta1](https://github.com/schmittjoh/JMSSerializerBundle/tree/3.0.0-beta1) (2018-09-12)

**Implemented enhancements:**

- Add autoconfigure for handlers [\#671](https://github.com/schmittjoh/JMSSerializerBundle/pull/671) ([magnetik](https://github.com/magnetik))

**Closed issues:**

- Hard cache removal - first JSON serialization has an unusual result [\#682](https://github.com/schmittjoh/JMSSerializerBundle/issues/682)
- Avoiding the circular reference stop [\#679](https://github.com/schmittjoh/JMSSerializerBundle/issues/679)
- . [\#673](https://github.com/schmittjoh/JMSSerializerBundle/issues/673)
- Annotation Group not working on Entity using Symfony 4 [\#669](https://github.com/schmittjoh/JMSSerializerBundle/issues/669)
- Regression since 2.4.1 [\#663](https://github.com/schmittjoh/JMSSerializerBundle/issues/663)
- Example code for "Changing the Object Constructor" section does not show on the web page. [\#601](https://github.com/schmittjoh/JMSSerializerBundle/issues/601)

**Merged pull requests:**

- Enhancement: Normalize composer.json [\#677](https://github.com/schmittjoh/JMSSerializerBundle/pull/677) ([localheinz](https://github.com/localheinz))
- Enhancement: Keep packages sorted in composer.json [\#676](https://github.com/schmittjoh/JMSSerializerBundle/pull/676) ([localheinz](https://github.com/localheinz))
- Fix Constructors configuration section [\#675](https://github.com/schmittjoh/JMSSerializerBundle/pull/675) ([kinow](https://github.com/kinow))

## [2.4.2](https://github.com/schmittjoh/JMSSerializerBundle/tree/2.4.2) (2018-06-19)

**Closed issues:**

- XML serialization version and encoding configuration [\#661](https://github.com/schmittjoh/JMSSerializerBundle/issues/661)
- \[2.4\] Stop-Watch-Listener is broken [\#660](https://github.com/schmittjoh/JMSSerializerBundle/issues/660)
- Change license to MIT [\#655](https://github.com/schmittjoh/JMSSerializerBundle/issues/655)
- Deserialize YAML [\#428](https://github.com/schmittjoh/JMSSerializerBundle/issues/428)
- Runtime Naming Strategy [\#347](https://github.com/schmittjoh/JMSSerializerBundle/issues/347)

**Merged pull requests:**

- Hotfix Stable solrting [\#664](https://github.com/schmittjoh/JMSSerializerBundle/pull/664) ([bpolaszek](https://github.com/bpolaszek))
- Moving to MIT license [\#659](https://github.com/schmittjoh/JMSSerializerBundle/pull/659) ([goetas](https://github.com/goetas))
- Compatibility with jms/serializer 2.0 [\#652](https://github.com/schmittjoh/JMSSerializerBundle/pull/652) ([goetas](https://github.com/goetas))

## [2.4.1](https://github.com/schmittjoh/JMSSerializerBundle/tree/2.4.1) (2018-05-25)

**Fixed bugs:**

- Can not register custom handler for multiple types since \#645 [\#656](https://github.com/schmittjoh/JMSSerializerBundle/issues/656)
- Fix serialization handler registration by priority [\#658](https://github.com/schmittjoh/JMSSerializerBundle/pull/658) ([goetas](https://github.com/goetas))

**Merged pull requests:**

- Fix serialization handler registration by priority [\#657](https://github.com/schmittjoh/JMSSerializerBundle/pull/657) ([discordier](https://github.com/discordier))

## [2.4.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/2.4.0) (2018-05-20)

**Implemented enhancements:**

- Proposal: Add priorities feature to handlers [\#645](https://github.com/schmittjoh/JMSSerializerBundle/pull/645) ([adiq](https://github.com/adiq))

**Closed issues:**

- Adding incrementing counter to each item in collection [\#653](https://github.com/schmittjoh/JMSSerializerBundle/issues/653)
- JMSSerializerBundle configuration Metadata \(2\) [\#650](https://github.com/schmittjoh/JMSSerializerBundle/issues/650)
- Without CDATA and without escaping [\#649](https://github.com/schmittjoh/JMSSerializerBundle/issues/649)
- JMS Serializer add groups by some condition [\#646](https://github.com/schmittjoh/JMSSerializerBundle/issues/646)
- Serializer ignores criteria query [\#643](https://github.com/schmittjoh/JMSSerializerBundle/issues/643)
- Unable to override default handlers in Symfony 4 [\#642](https://github.com/schmittjoh/JMSSerializerBundle/issues/642)
- jms\_serializer directory in cache is not writable [\#640](https://github.com/schmittjoh/JMSSerializerBundle/issues/640)
- Problem with version 1.2.0 and library JMS\Serializer [\#636](https://github.com/schmittjoh/JMSSerializerBundle/issues/636)
- Symfony 3.4 - jms\_serializer.json\_deserialization\_visitor service is private [\#632](https://github.com/schmittjoh/JMSSerializerBundle/issues/632)
- DateTime custom handler not working [\#631](https://github.com/schmittjoh/JMSSerializerBundle/issues/631)
- readOnly annotation with groups [\#628](https://github.com/schmittjoh/JMSSerializerBundle/issues/628)
- Tag v2.3.1 [\#627](https://github.com/schmittjoh/JMSSerializerBundle/issues/627)
- Subscribing Handler not used \(FOSRestBundle: ExceptionHandler\) [\#626](https://github.com/schmittjoh/JMSSerializerBundle/issues/626)
- Overwrite FormHandler [\#466](https://github.com/schmittjoh/JMSSerializerBundle/issues/466)

**Merged pull requests:**

- Update .travis.yml [\#637](https://github.com/schmittjoh/JMSSerializerBundle/pull/637) ([andreybolonin](https://github.com/andreybolonin))
- make serializer/deserializer visitor services public [\#635](https://github.com/schmittjoh/JMSSerializerBundle/pull/635) ([bgarel](https://github.com/bgarel))
- Add autoconfigure feature for sf =\> 3.3 [\#633](https://github.com/schmittjoh/JMSSerializerBundle/pull/633) ([juntereiner](https://github.com/juntereiner))
- drop getDefinition\(\) in favor of findDefinition\(\) [\#630](https://github.com/schmittjoh/JMSSerializerBundle/pull/630) ([xabbuh](https://github.com/xabbuh))

## [2.3.1](https://github.com/schmittjoh/JMSSerializerBundle/tree/2.3.1) (2017-12-08)

**Closed issues:**

- Move `symfony/stopwatch` to `require` instead of `require-dev` [\#624](https://github.com/schmittjoh/JMSSerializerBundle/issues/624)
- Packagist Issue [\#623](https://github.com/schmittjoh/JMSSerializerBundle/issues/623)

**Merged pull requests:**

- Extension \> Remove stopwatch subscriber when Stopwatch class is not found [\#625](https://github.com/schmittjoh/JMSSerializerBundle/pull/625) ([ruudk](https://github.com/ruudk))
- Update Packagist link [\#622](https://github.com/schmittjoh/JMSSerializerBundle/pull/622) ([thePanz](https://github.com/thePanz))

## [2.3.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/2.3.0) (2017-12-01)

**Implemented enhancements:**

- Cache warmup feature [\#615](https://github.com/schmittjoh/JMSSerializerBundle/pull/615) ([goetas](https://github.com/goetas))

**Closed issues:**

- Exclude property on serialization, but not on deserialization [\#619](https://github.com/schmittjoh/JMSSerializerBundle/issues/619)
- Stopwatch Subscriber not found \(SF4\) [\#617](https://github.com/schmittjoh/JMSSerializerBundle/issues/617)
- Problem deserialize xml with namespace: xmlns="http://www.w3.org/2000/09/xmldsig\#" in tag root [\#613](https://github.com/schmittjoh/JMSSerializerBundle/issues/613)
- Add support for kernel.cache\_warmer [\#611](https://github.com/schmittjoh/JMSSerializerBundle/issues/611)
- Symfony 4 - Class 'jms\_serializer.stopwatch\_subscriber' not found [\#610](https://github.com/schmittjoh/JMSSerializerBundle/issues/610)
- v2.0.0: The "name" property of directories must be given - but must we specify directories? [\#607](https://github.com/schmittjoh/JMSSerializerBundle/issues/607)
- Add symfony/translation to required bundles [\#606](https://github.com/schmittjoh/JMSSerializerBundle/issues/606)
- Integrate schmittjoh/serializer\#22 [\#603](https://github.com/schmittjoh/JMSSerializerBundle/issues/603)

**Merged pull requests:**

- Use stable symfony [\#621](https://github.com/schmittjoh/JMSSerializerBundle/pull/621) ([goetas](https://github.com/goetas))
- make it possible to decorate services [\#620](https://github.com/schmittjoh/JMSSerializerBundle/pull/620) ([xabbuh](https://github.com/xabbuh))
- support lazily loaded event listeners and handlers [\#618](https://github.com/schmittjoh/JMSSerializerBundle/pull/618) ([xabbuh](https://github.com/xabbuh))
- Symfony 4 issues with private aliases [\#616](https://github.com/schmittjoh/JMSSerializerBundle/pull/616) ([goetas](https://github.com/goetas))
- Full Symfony 4 compatibility [\#605](https://github.com/schmittjoh/JMSSerializerBundle/pull/605) ([goetas](https://github.com/goetas))

## [2.2.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/2.2.0) (2017-09-29)

**Implemented enhancements:**

- Injected validator.translation\_domain to FormErrorHandler [\#580](https://github.com/schmittjoh/JMSSerializerBundle/pull/580) ([prosalov](https://github.com/prosalov))

**Closed issues:**

- The FormErrorHandler forces the translation domain to 'validators' [\#501](https://github.com/schmittjoh/JMSSerializerBundle/issues/501)
- Documentation incorrectly states that a handler service can be private [\#260](https://github.com/schmittjoh/JMSSerializerBundle/issues/260)

**Merged pull requests:**

- Remove dependencies from translator and form handler [\#604](https://github.com/schmittjoh/JMSSerializerBundle/pull/604) ([goetas](https://github.com/goetas))

## [2.1.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/2.1.0) (2017-08-31)

**Implemented enhancements:**

- Allow event listener/susbcriber services to be private [\#593](https://github.com/schmittjoh/JMSSerializerBundle/issues/593)
- Date deserialization \(DateTime object\) [\#582](https://github.com/schmittjoh/JMSSerializerBundle/issues/582)
- Allow listeners and event subscribers to be private [\#594](https://github.com/schmittjoh/JMSSerializerBundle/pull/594) ([goetas](https://github.com/goetas))

**Fixed bugs:**

- Handler DI [\#598](https://github.com/schmittjoh/JMSSerializerBundle/issues/598)
- Allow subscribing handlers to be private [\#602](https://github.com/schmittjoh/JMSSerializerBundle/pull/602) ([goetas](https://github.com/goetas))

**Closed issues:**

- Gedmo Unrecognized field: createdAt - Error! [\#599](https://github.com/schmittjoh/JMSSerializerBundle/issues/599)
- Array deserialization problem [\#597](https://github.com/schmittjoh/JMSSerializerBundle/issues/597)
- Integrate JMSSerializer into Symfony PropertyInfo? [\#591](https://github.com/schmittjoh/JMSSerializerBundle/issues/591)
- Yml config should allow to configure the default accessType and readOnly options [\#586](https://github.com/schmittjoh/JMSSerializerBundle/issues/586)
- Can't use symfony serializer when requesting the id "serializer" [\#583](https://github.com/schmittjoh/JMSSerializerBundle/issues/583)
- XML Collection names and null values [\#581](https://github.com/schmittjoh/JMSSerializerBundle/issues/581)
- Not work for Symfony 3.3? [\#579](https://github.com/schmittjoh/JMSSerializerBundle/issues/579)
- possible to register a handler that will compare against abstract classes [\#577](https://github.com/schmittjoh/JMSSerializerBundle/issues/577)

**Merged pull requests:**

- Added assertions to private services tests [\#595](https://github.com/schmittjoh/JMSSerializerBundle/pull/595) ([bgaleotti](https://github.com/bgaleotti))
- Change min stability [\#592](https://github.com/schmittjoh/JMSSerializerBundle/pull/592) ([goetas](https://github.com/goetas))
- install stable dependencies when possible [\#588](https://github.com/schmittjoh/JMSSerializerBundle/pull/588) ([xabbuh](https://github.com/xabbuh))
- \[Composer\] Upgrade required php version [\#587](https://github.com/schmittjoh/JMSSerializerBundle/pull/587) ([lchrusciel](https://github.com/lchrusciel))
- Run tests on ubuntu trusty [\#585](https://github.com/schmittjoh/JMSSerializerBundle/pull/585) ([goetas](https://github.com/goetas))
- Improve the autowiring configuration [\#584](https://github.com/schmittjoh/JMSSerializerBundle/pull/584) ([stof](https://github.com/stof))
- compatibility with Symfony 4 [\#576](https://github.com/schmittjoh/JMSSerializerBundle/pull/576) ([xabbuh](https://github.com/xabbuh))

## [2.0.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/2.0.0) (2017-05-17)

**Closed issues:**

- Remove short "serializer" alias [\#558](https://github.com/schmittjoh/JMSSerializerBundle/issues/558)
- Check for broken serialization metadata mappings [\#534](https://github.com/schmittjoh/JMSSerializerBundle/issues/534)
- Serializing traits with JMSSerializer and YAML [\#424](https://github.com/schmittjoh/JMSSerializerBundle/issues/424)
- Add kernel.cache\_clearer and/or kernel.cache\_warmer support [\#415](https://github.com/schmittjoh/JMSSerializerBundle/issues/415)

**Merged pull requests:**

- Preparing 2.0 [\#571](https://github.com/schmittjoh/JMSSerializerBundle/pull/571) ([goetas](https://github.com/goetas))

## [1.5.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/1.5.0) (2017-05-10)

**Implemented enhancements:**

- Added configuration options for recent doctrine improvements [\#570](https://github.com/schmittjoh/JMSSerializerBundle/pull/570) ([goetas](https://github.com/goetas))
- Allow autowiring serializer [\#568](https://github.com/schmittjoh/JMSSerializerBundle/pull/568) ([Tobion](https://github.com/Tobion))
- Added runtime twig extension support \(significant performance improvements\) [\#563](https://github.com/schmittjoh/JMSSerializerBundle/pull/563) ([goetas](https://github.com/goetas))

**Fixed bugs:**

- Arrays beginning with index 1 are parsed as an object  [\#375](https://github.com/schmittjoh/JMSSerializerBundle/issues/375)
- serializing a json array using {} instead of \[\] [\#373](https://github.com/schmittjoh/JMSSerializerBundle/issues/373)

**Closed issues:**

- \[BUG\] Metadata PhpDriver always beats AnnotationDriver in DriverChain [\#567](https://github.com/schmittjoh/JMSSerializerBundle/issues/567)
-  requirements could not be resolved to an installable set of packages [\#566](https://github.com/schmittjoh/JMSSerializerBundle/issues/566)
- Missing configuration for doctrine object constructor [\#565](https://github.com/schmittjoh/JMSSerializerBundle/issues/565)
- Performance issue [\#562](https://github.com/schmittjoh/JMSSerializerBundle/issues/562)
- Missing configuration option for lazy virtual proxy initialization [\#539](https://github.com/schmittjoh/JMSSerializerBundle/issues/539)
- SerializationListener not being called for sub entities after upgrading to 1.1.0 [\#514](https://github.com/schmittjoh/JMSSerializerBundle/issues/514)
- Can't override third party serializer config file [\#511](https://github.com/schmittjoh/JMSSerializerBundle/issues/511)
- registering callback with a specific class name doesn't work . [\#508](https://github.com/schmittjoh/JMSSerializerBundle/issues/508)
- change serialized name of a property when it is in a specific group [\#457](https://github.com/schmittjoh/JMSSerializerBundle/issues/457)
- Serializing stdClass with arbitrary depth [\#414](https://github.com/schmittjoh/JMSSerializerBundle/issues/414)
- Usage of @Groups [\#382](https://github.com/schmittjoh/JMSSerializerBundle/issues/382)
- Serializing a stdClass [\#158](https://github.com/schmittjoh/JMSSerializerBundle/issues/158)
- Add support for Traits \(PHP 5.4\) [\#102](https://github.com/schmittjoh/JMSSerializerBundle/issues/102)
- @ExclusionPolicy\("all"\) is not respected by the parent classes [\#100](https://github.com/schmittjoh/JMSSerializerBundle/issues/100)

## [1.4.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/1.4.0) (2017-04-10)

**Fixed bugs:**

- Alias not working any more with 1.3 [\#559](https://github.com/schmittjoh/JMSSerializerBundle/issues/559)

**Closed issues:**

- Document how to prevent JMS serializer from overriding Symfony serializer [\#513](https://github.com/schmittjoh/JMSSerializerBundle/issues/513)
- AccessorOrder : properties vs virtualProperty [\#512](https://github.com/schmittjoh/JMSSerializerBundle/issues/512)
- Specify @Serializer/Group for @Discriminator field  [\#506](https://github.com/schmittjoh/JMSSerializerBundle/issues/506)
- Ignore entity and/or subentity based on attribute value [\#499](https://github.com/schmittjoh/JMSSerializerBundle/issues/499)
- How to change Strategy [\#493](https://github.com/schmittjoh/JMSSerializerBundle/issues/493)
- AccessorOrder is ignored [\#488](https://github.com/schmittjoh/JMSSerializerBundle/issues/488)
- When serializing subentities discriminator value is always added to serialized output [\#479](https://github.com/schmittjoh/JMSSerializerBundle/issues/479)
- Serialized name "id" [\#461](https://github.com/schmittjoh/JMSSerializerBundle/issues/461)
- Jms serializer @JMS\Inline\(\) annotation overrides an actual id [\#460](https://github.com/schmittjoh/JMSSerializerBundle/issues/460)
- @JMS\Serializer\Annotation\Type\("DateTime\<'c'\>"\) doesn't works properly [\#459](https://github.com/schmittjoh/JMSSerializerBundle/issues/459)
- Custom Class Type Mapping [\#446](https://github.com/schmittjoh/JMSSerializerBundle/issues/446)
- Return an array instead of object [\#439](https://github.com/schmittjoh/JMSSerializerBundle/issues/439)
- Serializer does not seem to visit virtual properties recursively [\#429](https://github.com/schmittjoh/JMSSerializerBundle/issues/429)
- filtering doctrine entites with  `ExclusionPolicy\(All\)` [\#419](https://github.com/schmittjoh/JMSSerializerBundle/issues/419)
- \[Feature\] Apply exclusión policity by groups [\#401](https://github.com/schmittjoh/JMSSerializerBundle/issues/401)
- DateTime ISO8601 in PHP doesnt support milliseconds [\#395](https://github.com/schmittjoh/JMSSerializerBundle/issues/395)
- custom handler documentation [\#379](https://github.com/schmittjoh/JMSSerializerBundle/issues/379)
- Question: How to serialize data for KNP paginator which is using Solarium Subscriber. [\#374](https://github.com/schmittjoh/JMSSerializerBundle/issues/374)
- Error View::create [\#365](https://github.com/schmittjoh/JMSSerializerBundle/issues/365)
- Propel Collection Handler is not registered [\#349](https://github.com/schmittjoh/JMSSerializerBundle/issues/349)
- Cannot redeclare class doctrine\orm\mapping\annotation [\#339](https://github.com/schmittjoh/JMSSerializerBundle/issues/339)
- get a error of JMSSerializerBundle in Symfony2.3.4 and ODM [\#320](https://github.com/schmittjoh/JMSSerializerBundle/issues/320)
- DoctrineObjectConstructor doesn't work with id as attribute [\#305](https://github.com/schmittjoh/JMSSerializerBundle/issues/305)
- Performance issue - Help needed for optimization [\#281](https://github.com/schmittjoh/JMSSerializerBundle/issues/281)
- GraphNavigator - Marked as Final  [\#238](https://github.com/schmittjoh/JMSSerializerBundle/issues/238)
- Error when using yml for FormErrors [\#221](https://github.com/schmittjoh/JMSSerializerBundle/issues/221)
- Can't configurate class which is in global namespace with YAML or XML [\#217](https://github.com/schmittjoh/JMSSerializerBundle/issues/217)
- VirtualProperties called after postSerialize triggers [\#216](https://github.com/schmittjoh/JMSSerializerBundle/issues/216)

**Merged pull requests:**

- Use svg build badge [\#560](https://github.com/schmittjoh/JMSSerializerBundle/pull/560) ([hanneskaeufler](https://github.com/hanneskaeufler))

## [1.3.1](https://github.com/schmittjoh/JMSSerializerBundle/tree/1.3.1) (2017-03-29)

**Implemented enhancements:**

- Added configuration for default context [\#556](https://github.com/schmittjoh/JMSSerializerBundle/pull/556) ([edefimov](https://github.com/edefimov))
- add service definition for IdenticalPropertyNamingStrategy [\#445](https://github.com/schmittjoh/JMSSerializerBundle/pull/445) ([maff](https://github.com/maff))

**Closed issues:**

- What's the purpose of 'id' under 'property\_naming' namespace in configuration. [\#522](https://github.com/schmittjoh/JMSSerializerBundle/issues/522)
- "fos\_rest.serializer" must implement FOS\RestBundle\Serializer\Serializer \(instance of "JMS\Serializer\Serializer" given\). [\#509](https://github.com/schmittjoh/JMSSerializerBundle/issues/509)
- property\_naming.id is not used? Property way to override the naming strategy [\#449](https://github.com/schmittjoh/JMSSerializerBundle/issues/449)
- add option to alias field names [\#433](https://github.com/schmittjoh/JMSSerializerBundle/issues/433)

**Merged pull requests:**

- Fix "Changing the Object Constructor" doc samples [\#359](https://github.com/schmittjoh/JMSSerializerBundle/pull/359) ([hanneskaeufler](https://github.com/hanneskaeufler))

## [1.3.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/1.3.0) (2017-03-28)

**Closed issues:**

- Force uppermost level of output json as object [\#555](https://github.com/schmittjoh/JMSSerializerBundle/issues/555)
- PreSerializationListener not being called anymore after updating [\#554](https://github.com/schmittjoh/JMSSerializerBundle/issues/554)
- Are there any way to select entity fields dinamically? [\#496](https://github.com/schmittjoh/JMSSerializerBundle/issues/496)
- Ability to add custom exclusion strategies and override existing one [\#489](https://github.com/schmittjoh/JMSSerializerBundle/issues/489)
- On virtual properties would be great to be able to use symfony language expression [\#403](https://github.com/schmittjoh/JMSSerializerBundle/issues/403)
- Get metadata from doctrine ORM when PHPCR-ODM is present not working [\#389](https://github.com/schmittjoh/JMSSerializerBundle/issues/389)
- Symfony 2.3 Form serialization [\#309](https://github.com/schmittjoh/JMSSerializerBundle/issues/309)

**Merged pull requests:**

- Expression language tests [\#553](https://github.com/schmittjoh/JMSSerializerBundle/pull/553) ([goetas](https://github.com/goetas))
- Added handler tests [\#552](https://github.com/schmittjoh/JMSSerializerBundle/pull/552) ([goetas](https://github.com/goetas))
- Allow service parameters in listener class name [\#551](https://github.com/schmittjoh/JMSSerializerBundle/pull/551) ([goetas](https://github.com/goetas))
- Fix metadata directories loading where path is just a bundle name [\#550](https://github.com/schmittjoh/JMSSerializerBundle/pull/550) ([goetas](https://github.com/goetas))
- Remove unused params in visitors [\#549](https://github.com/schmittjoh/JMSSerializerBundle/pull/549) ([goetas](https://github.com/goetas))
- Expression language based virtual properties [\#545](https://github.com/schmittjoh/JMSSerializerBundle/pull/545) ([goetas](https://github.com/goetas))

## [1.2.1](https://github.com/schmittjoh/JMSSerializerBundle/tree/1.2.1) (2017-03-13)

**Closed issues:**

- After updating XmlList stop working  [\#548](https://github.com/schmittjoh/JMSSerializerBundle/issues/548)
- serialize arrays \(instead of objects\) to json [\#426](https://github.com/schmittjoh/JMSSerializerBundle/issues/426)

**Merged pull requests:**

- Release v1.2.0 preview [\#546](https://github.com/schmittjoh/JMSSerializerBundle/pull/546) ([goetas](https://github.com/goetas))

## [1.2.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/1.2.0) (2017-02-22)

**Implemented enhancements:**

- Added expression language support [\#544](https://github.com/schmittjoh/JMSSerializerBundle/pull/544) ([goetas](https://github.com/goetas))
- Context factories [\#543](https://github.com/schmittjoh/JMSSerializerBundle/pull/543) ([goetas](https://github.com/goetas))
- Add "formatOutput" option to DI [\#527](https://github.com/schmittjoh/JMSSerializerBundle/pull/527) ([AyrtonRicardo](https://github.com/AyrtonRicardo))

**Closed issues:**

- All services are broken [\#541](https://github.com/schmittjoh/JMSSerializerBundle/issues/541)
- Blank XML breaks XmlDeserializationVisitor error handling [\#540](https://github.com/schmittjoh/JMSSerializerBundle/issues/540)
- Customize the serialization of an empty string for XML [\#538](https://github.com/schmittjoh/JMSSerializerBundle/issues/538)
- Symfony 3.1 support [\#533](https://github.com/schmittjoh/JMSSerializerBundle/issues/533)
- Warning: JMS\Serializer\XmlDeserializationVisitor::visitArray\(\): Node no longer exists   [\#532](https://github.com/schmittjoh/JMSSerializerBundle/issues/532)
- Configurable defaults \(groups\) for serialization [\#530](https://github.com/schmittjoh/JMSSerializerBundle/issues/530)
- Setup Problem [\#525](https://github.com/schmittjoh/JMSSerializerBundle/issues/525)
- Symfony 3 'json' method gives errors with JMS [\#523](https://github.com/schmittjoh/JMSSerializerBundle/issues/523)
- Unable to install with composer :\( [\#520](https://github.com/schmittjoh/JMSSerializerBundle/issues/520)
- Unable to override default handlers  [\#519](https://github.com/schmittjoh/JMSSerializerBundle/issues/519)
- Error in security.access.decision\_manager service in Symfony 3.1 [\#518](https://github.com/schmittjoh/JMSSerializerBundle/issues/518)
- Twig deprecation in Serializer  [\#516](https://github.com/schmittjoh/JMSSerializerBundle/issues/516)
- Exclude the "discriminator" column for serialized entities [\#515](https://github.com/schmittjoh/JMSSerializerBundle/issues/515)
- Installation issues using composer [\#507](https://github.com/schmittjoh/JMSSerializerBundle/issues/507)
- PHP7 compatibility [\#502](https://github.com/schmittjoh/JMSSerializerBundle/issues/502)
- Float with no decimals are automatically converted to int [\#497](https://github.com/schmittjoh/JMSSerializerBundle/issues/497)
- Default context for serialization [\#495](https://github.com/schmittjoh/JMSSerializerBundle/issues/495)
- If no group is defined on SerializationContext all fields with exposed true for groups are exposed [\#491](https://github.com/schmittjoh/JMSSerializerBundle/issues/491)
- Select attributes in a self referencing field [\#480](https://github.com/schmittjoh/JMSSerializerBundle/issues/480)
- The document for yml configuration is confusing， why yml configuration is not working? [\#477](https://github.com/schmittjoh/JMSSerializerBundle/issues/477)
- Fix PHP requirements inside composer.json [\#465](https://github.com/schmittjoh/JMSSerializerBundle/issues/465)
- Serialize entity with Router dependency [\#458](https://github.com/schmittjoh/JMSSerializerBundle/issues/458)
- Enable @Expose on code [\#456](https://github.com/schmittjoh/JMSSerializerBundle/issues/456)
- Custom handlers on primitive values [\#455](https://github.com/schmittjoh/JMSSerializerBundle/issues/455)
- Add server url to image on serialization [\#451](https://github.com/schmittjoh/JMSSerializerBundle/issues/451)
- PHP Fatal error:  Using $this when not in object context in JMS/Serializer/Serializer.php on line 99 [\#450](https://github.com/schmittjoh/JMSSerializerBundle/issues/450)
- Problem with namespace for XMLList [\#438](https://github.com/schmittjoh/JMSSerializerBundle/issues/438)
- Discriminator bug fix by upgrading required src [\#423](https://github.com/schmittjoh/JMSSerializerBundle/issues/423)
- Exclude based on condition [\#422](https://github.com/schmittjoh/JMSSerializerBundle/issues/422)
- When serialized collection of extendend objects returns {/\*...\*/} and must be \[/\*..\*/\] [\#418](https://github.com/schmittjoh/JMSSerializerBundle/issues/418)
- Serialization manyToOne with yml configutaion [\#411](https://github.com/schmittjoh/JMSSerializerBundle/issues/411)
- Can I set JSON\_FORCE\_OBJECT for 1 specific serialization, not for the entire project in config.yml ? [\#410](https://github.com/schmittjoh/JMSSerializerBundle/issues/410)
- Fatal error: Invalid opcode 153/1/8 [\#408](https://github.com/schmittjoh/JMSSerializerBundle/issues/408)
- if DiExtraBundle not available, JMSSerializer crash [\#392](https://github.com/schmittjoh/JMSSerializerBundle/issues/392)
- Wrong Json Response  [\#387](https://github.com/schmittjoh/JMSSerializerBundle/issues/387)
- custom type using a custom handler for primitives [\#385](https://github.com/schmittjoh/JMSSerializerBundle/issues/385)
- Serialize custom properties of entities [\#377](https://github.com/schmittjoh/JMSSerializerBundle/issues/377)
- Reflection Exception after upgrade from 0.14.0 to 0.15.0 [\#369](https://github.com/schmittjoh/JMSSerializerBundle/issues/369)
- ServiceNotFoundException: You have requested a non-existent service \"serializer\" [\#368](https://github.com/schmittjoh/JMSSerializerBundle/issues/368)
- Question: How can i generate formated response for  JSON. [\#367](https://github.com/schmittjoh/JMSSerializerBundle/issues/367)
- Include Entity Name in JSON [\#364](https://github.com/schmittjoh/JMSSerializerBundle/issues/364)
- serialize\_null setting without effect for arrays containing NULL values? [\#361](https://github.com/schmittjoh/JMSSerializerBundle/issues/361)
- \[Feature request\] Relations Serialization Strategy [\#358](https://github.com/schmittjoh/JMSSerializerBundle/issues/358)
- Documentation error for installation in AppKernel.php:  [\#354](https://github.com/schmittjoh/JMSSerializerBundle/issues/354)
- Expected object but got array when serialize ArrayCollection of intermediate entity. [\#350](https://github.com/schmittjoh/JMSSerializerBundle/issues/350)
- 0.13 stable tag [\#344](https://github.com/schmittjoh/JMSSerializerBundle/issues/344)
- Composer error minimum-stability [\#330](https://github.com/schmittjoh/JMSSerializerBundle/issues/330)
- Unexpected extra requests when working with doctrine tree extension. [\#326](https://github.com/schmittjoh/JMSSerializerBundle/issues/326)
- Maximum execution time of 30 seconds exceeded [\#306](https://github.com/schmittjoh/JMSSerializerBundle/issues/306)
- @Accessor not works in sf2.3 [\#304](https://github.com/schmittjoh/JMSSerializerBundle/issues/304)
- Instalation in symfony 2.3 [\#301](https://github.com/schmittjoh/JMSSerializerBundle/issues/301)
- Doctrine entity Symfony2 [\#288](https://github.com/schmittjoh/JMSSerializerBundle/issues/288)
- serialize\_null configuration [\#287](https://github.com/schmittjoh/JMSSerializerBundle/issues/287)
- Fatal Error: Cannot redeclare class doctrine\orm\mapping\annotation in /vagrant/vendor/composer/ClassLoader.php line 183 [\#286](https://github.com/schmittjoh/JMSSerializerBundle/issues/286)
- Unable to install on Symfony 2.2 [\#285](https://github.com/schmittjoh/JMSSerializerBundle/issues/285)
- FatalErrorException: Error: Call to undefined method JMS\Serializer\SerializerBuilder::serialize\(\) [\#278](https://github.com/schmittjoh/JMSSerializerBundle/issues/278)
- Add support for wildcard serialization group [\#269](https://github.com/schmittjoh/JMSSerializerBundle/issues/269)
- Prevent Doctrine Entities from Serializing [\#263](https://github.com/schmittjoh/JMSSerializerBundle/issues/263)
- XML serialization of URLs fail [\#230](https://github.com/schmittjoh/JMSSerializerBundle/issues/230)
- custom visitor [\#218](https://github.com/schmittjoh/JMSSerializerBundle/issues/218)

**Merged pull requests:**

- Improve build info and CI [\#542](https://github.com/schmittjoh/JMSSerializerBundle/pull/542) ([goetas](https://github.com/goetas))
- Fix bad Tag name in error message [\#454](https://github.com/schmittjoh/JMSSerializerBundle/pull/454) ([lemoinem](https://github.com/lemoinem))

## [1.1.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/1.1.0) (2015-11-10)

**Closed issues:**

- Twig\_Function\_Method class is deprecated since version 1.12 [\#490](https://github.com/schmittjoh/JMSSerializerBundle/issues/490)
- Depreciated Twig calls [\#487](https://github.com/schmittjoh/JMSSerializerBundle/issues/487)
- JMSSerializerBundle and FOSUser not working [\#486](https://github.com/schmittjoh/JMSSerializerBundle/issues/486)
- Documentation is down [\#481](https://github.com/schmittjoh/JMSSerializerBundle/issues/481)
- Eror while composer install [\#478](https://github.com/schmittjoh/JMSSerializerBundle/issues/478)
- GenericSerializationVisitor::addData ALWAYS throws Exception [\#473](https://github.com/schmittjoh/JMSSerializerBundle/issues/473)
- Folder \(or namespace\) issue on subscriber [\#472](https://github.com/schmittjoh/JMSSerializerBundle/issues/472)
- PreSerialize + Groups [\#471](https://github.com/schmittjoh/JMSSerializerBundle/issues/471)
- Fails to serialize after deleting or renaming a field.  [\#467](https://github.com/schmittjoh/JMSSerializerBundle/issues/467)
- JMSSerializerBundle overwriting Symfony's Serializer without configurable option [\#462](https://github.com/schmittjoh/JMSSerializerBundle/issues/462)
- Tag [\#421](https://github.com/schmittjoh/JMSSerializerBundle/issues/421)

**Merged pull requests:**

- enabled Symfony 3.0 support [\#492](https://github.com/schmittjoh/JMSSerializerBundle/pull/492) ([lsmith77](https://github.com/lsmith77))
- Add PHP 7 on Travis [\#474](https://github.com/schmittjoh/JMSSerializerBundle/pull/474) ([Soullivaneuh](https://github.com/Soullivaneuh))
- add enable\_short\_alias in YAML config reference [\#463](https://github.com/schmittjoh/JMSSerializerBundle/pull/463) ([ghost](https://github.com/ghost))
- Fix jms\_serializer.infer\_types\_from\_doctrine\_metadata usage [\#430](https://github.com/schmittjoh/JMSSerializerBundle/pull/430) ([magnetik](https://github.com/magnetik))

## [1.0.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/1.0.0) (2015-06-23)

**Closed issues:**

- Not. [\#443](https://github.com/schmittjoh/JMSSerializerBundle/issues/443)
- Cache Warmup [\#441](https://github.com/schmittjoh/JMSSerializerBundle/issues/441)
- SerializeNull [\#440](https://github.com/schmittjoh/JMSSerializerBundle/issues/440)
- Expose the same entity as part of child attributes [\#420](https://github.com/schmittjoh/JMSSerializerBundle/issues/420)
- PreSerialize Proxy Class [\#407](https://github.com/schmittjoh/JMSSerializerBundle/issues/407)
- Overwriting Properties in different API Versions [\#406](https://github.com/schmittjoh/JMSSerializerBundle/issues/406)
- DateTime deserialization does not work properly. [\#400](https://github.com/schmittjoh/JMSSerializerBundle/issues/400)
- Using jms\_serializer.cache\_naming\_strategy.class overrides @SerializedName annotation [\#397](https://github.com/schmittjoh/JMSSerializerBundle/issues/397)
- Naming strategy for embedded form [\#396](https://github.com/schmittjoh/JMSSerializerBundle/issues/396)
- Warning: Erroneous data format for unserializing object constructor [\#394](https://github.com/schmittjoh/JMSSerializerBundle/issues/394)
- Discriminator over attribute [\#393](https://github.com/schmittjoh/JMSSerializerBundle/issues/393)
- Update composer to request for a newer version of serializer [\#388](https://github.com/schmittjoh/JMSSerializerBundle/issues/388)
- Anotation "@Enum" was never imported error [\#380](https://github.com/schmittjoh/JMSSerializerBundle/issues/380)
- Cannot pass shorthand entity class name to deserialize method [\#378](https://github.com/schmittjoh/JMSSerializerBundle/issues/378)
- Deserialize json array to array of entities [\#376](https://github.com/schmittjoh/JMSSerializerBundle/issues/376)
- Unable to use CustomHandler [\#370](https://github.com/schmittjoh/JMSSerializerBundle/issues/370)
- Serialization of array of entities [\#363](https://github.com/schmittjoh/JMSSerializerBundle/issues/363)
- Data filtering in related entity [\#362](https://github.com/schmittjoh/JMSSerializerBundle/issues/362)
- boolean values not serialized when value is null.  [\#356](https://github.com/schmittjoh/JMSSerializerBundle/issues/356)
- Add support for \Serializable objects [\#355](https://github.com/schmittjoh/JMSSerializerBundle/issues/355)
- perform default deserialization in the custom handler [\#353](https://github.com/schmittjoh/JMSSerializerBundle/issues/353)
- @Type resolve interfaces [\#352](https://github.com/schmittjoh/JMSSerializerBundle/issues/352)
- Class 'DoctrinePHPCRTypeDriver' not found [\#346](https://github.com/schmittjoh/JMSSerializerBundle/issues/346)
- YAML/XML Reference and class inheritance  [\#333](https://github.com/schmittjoh/JMSSerializerBundle/issues/333)
- Can't define a virtual property in a xml config file [\#316](https://github.com/schmittjoh/JMSSerializerBundle/issues/316)
- SerializeNull documentation [\#276](https://github.com/schmittjoh/JMSSerializerBundle/issues/276)
- Detecting serializion groups in the serialize listeners [\#264](https://github.com/schmittjoh/JMSSerializerBundle/issues/264)

**Merged pull requests:**

- \[Travis\] test lowest dependencies [\#444](https://github.com/schmittjoh/JMSSerializerBundle/pull/444) ([boekkooi](https://github.com/boekkooi))
- ContextErrorException: call\_user\_func\(\) expects parameter 1 to be... [\#435](https://github.com/schmittjoh/JMSSerializerBundle/pull/435) ([umpirsky](https://github.com/umpirsky))
- Fix error in code [\#417](https://github.com/schmittjoh/JMSSerializerBundle/pull/417) ([wouterj](https://github.com/wouterj))
- Applied standard installation template [\#416](https://github.com/schmittjoh/JMSSerializerBundle/pull/416) ([wouterj](https://github.com/wouterj))
- \[Doc - Configuration\] Fix yaml code-block [\#402](https://github.com/schmittjoh/JMSSerializerBundle/pull/402) ([Peekmo](https://github.com/Peekmo))
- Added more PHP versions and HHVM [\#399](https://github.com/schmittjoh/JMSSerializerBundle/pull/399) ([Nyholm](https://github.com/Nyholm))
- Documentation Fix: When registering an event listener, you have to additional attribute "cl... [\#360](https://github.com/schmittjoh/JMSSerializerBundle/pull/360) ([epicwhale](https://github.com/epicwhale))

## [0.13.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/0.13.0) (2013-12-05)

**Closed issues:**

- "array" type: Not working for arrays of DateTime objects [\#343](https://github.com/schmittjoh/JMSSerializerBundle/issues/343)
- Annotation which would generate subresource's uri instead of subresource's properties for many-to-one relations [\#342](https://github.com/schmittjoh/JMSSerializerBundle/issues/342)
- Add documentation mirror [\#338](https://github.com/schmittjoh/JMSSerializerBundle/issues/338)
- Custom\_Handlers Documentation Old/Outdated? [\#337](https://github.com/schmittjoh/JMSSerializerBundle/issues/337)
- Please update to use newer parser version [\#335](https://github.com/schmittjoh/JMSSerializerBundle/issues/335)
- How to get data in the service onPostSerialize? [\#331](https://github.com/schmittjoh/JMSSerializerBundle/issues/331)
- method setGroup not find in web service Rest [\#329](https://github.com/schmittjoh/JMSSerializerBundle/issues/329)
- symfony 2.1 to 2.2 [\#328](https://github.com/schmittjoh/JMSSerializerBundle/issues/328)
- Undefined method getChildren Symfony 2.3 [\#327](https://github.com/schmittjoh/JMSSerializerBundle/issues/327)
- Serialization die because memory consumption [\#324](https://github.com/schmittjoh/JMSSerializerBundle/issues/324)
- Clarify the documentation ! [\#323](https://github.com/schmittjoh/JMSSerializerBundle/issues/323)
- recursion detected in JsonSerializationVisitor.php on line 29 [\#322](https://github.com/schmittjoh/JMSSerializerBundle/issues/322)
- Property serialized as object instead of array [\#321](https://github.com/schmittjoh/JMSSerializerBundle/issues/321)
- Can't install in Symfony 2.3.3 [\#319](https://github.com/schmittjoh/JMSSerializerBundle/issues/319)
- Version 0.12 [\#318](https://github.com/schmittjoh/JMSSerializerBundle/issues/318)
- Serializer doesn't serialize nested objects correctly [\#317](https://github.com/schmittjoh/JMSSerializerBundle/issues/317)
- Annotation MaxDepth [\#315](https://github.com/schmittjoh/JMSSerializerBundle/issues/315)
- How use the @Groups ? [\#314](https://github.com/schmittjoh/JMSSerializerBundle/issues/314)
- remove JMSDiExtraBundle dependency [\#294](https://github.com/schmittjoh/JMSSerializerBundle/issues/294)
- How can I force a property to serialize even if it's null? [\#293](https://github.com/schmittjoh/JMSSerializerBundle/issues/293)
- No handling of namespaces [\#135](https://github.com/schmittjoh/JMSSerializerBundle/issues/135)

**Merged pull requests:**

- Add support for cdata parameter on DateTimeHandler [\#345](https://github.com/schmittjoh/JMSSerializerBundle/pull/345) ([mvrhov](https://github.com/mvrhov))
- composer is preinstalled on travis [\#341](https://github.com/schmittjoh/JMSSerializerBundle/pull/341) ([lsmith77](https://github.com/lsmith77))
- \[WIP\] added support for PHPCR [\#340](https://github.com/schmittjoh/JMSSerializerBundle/pull/340) ([lsmith77](https://github.com/lsmith77))
- Integrate serialization with the stopwatch [\#334](https://github.com/schmittjoh/JMSSerializerBundle/pull/334) ([adrienbrault](https://github.com/adrienbrault))
- Added instructions to change the object constructor [\#325](https://github.com/schmittjoh/JMSSerializerBundle/pull/325) ([pkruithof](https://github.com/pkruithof))
- fix typo in RegisterEventListenersAndSubscribersPass [\#310](https://github.com/schmittjoh/JMSSerializerBundle/pull/310) ([i4got10](https://github.com/i4got10))
- Fixed namespace in installation instructions [\#279](https://github.com/schmittjoh/JMSSerializerBundle/pull/279) ([mweimerskirch](https://github.com/mweimerskirch))

## [0.12.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/0.12.0) (2013-07-29)

**Closed issues:**

- @ExclusionPolicy\("all"\) is not respected by the parent classes [\#311](https://github.com/schmittjoh/JMSSerializerBundle/issues/311)
- Symfony 2.1 - can't install jmsSerializerBundle via composer [\#307](https://github.com/schmittjoh/JMSSerializerBundle/issues/307)
- Json encode problem : array\(0 =\> 'A', 1 =\> 'B'\) =\> \['A', 'B'\] array\(1 =\> 'B', 2 =\> 'C'\) =\> {1: "B", 2: "C" [\#302](https://github.com/schmittjoh/JMSSerializerBundle/issues/302)
- Custom property naming strategy [\#300](https://github.com/schmittjoh/JMSSerializerBundle/issues/300)
- ReflectionException: Class JMS\Serializer\EventDispatcher\Subscriber\DoctrineProxySubscriber does not exist [\#297](https://github.com/schmittjoh/JMSSerializerBundle/issues/297)
- Error on last composer update : jms/serializer lib removed ! [\#295](https://github.com/schmittjoh/JMSSerializerBundle/issues/295)
- Output JSON and Specialchars  [\#289](https://github.com/schmittjoh/JMSSerializerBundle/issues/289)
- Symfony 2.2 - can't install jmsSerializerBundle via composer [\#283](https://github.com/schmittjoh/JMSSerializerBundle/issues/283)
- The annotation "@JMS\SerializerBundle\Annotation\ExclusionPolicy" does not exist [\#282](https://github.com/schmittjoh/JMSSerializerBundle/issues/282)
- 'public\_method' access requires redundant property in class [\#280](https://github.com/schmittjoh/JMSSerializerBundle/issues/280)
- 0.11.0 and Symfony 2.2  [\#274](https://github.com/schmittjoh/JMSSerializerBundle/issues/274)
- Exclusion Policies aren't properly applied when "serializeNull" is "true" [\#272](https://github.com/schmittjoh/JMSSerializerBundle/issues/272)
- Empty Objects get serialized as "array\(\)" [\#271](https://github.com/schmittjoh/JMSSerializerBundle/issues/271)
- Can't use bundle with Symfony 2.1 [\#268](https://github.com/schmittjoh/JMSSerializerBundle/issues/268)
- xml-root-name not working [\#262](https://github.com/schmittjoh/JMSSerializerBundle/issues/262)

**Merged pull requests:**

- make JMSDiExtraBundle dependency optional [\#313](https://github.com/schmittjoh/JMSSerializerBundle/pull/313) ([lsmith77](https://github.com/lsmith77))
- Update composer.json [\#303](https://github.com/schmittjoh/JMSSerializerBundle/pull/303) ([blaugueux](https://github.com/blaugueux))
- Typo in UPGRADING.md [\#298](https://github.com/schmittjoh/JMSSerializerBundle/pull/298) ([biozshock](https://github.com/biozshock))
- Fixed serializer library reference in PHP templating helper. [\#277](https://github.com/schmittjoh/JMSSerializerBundle/pull/277) ([rafalwrzeszcz](https://github.com/rafalwrzeszcz))
- Fixed the composer requirements [\#275](https://github.com/schmittjoh/JMSSerializerBundle/pull/275) ([stof](https://github.com/stof))

## [0.11.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/0.11.0) (2013-02-12)

**Closed issues:**

- Exclusion policy for entity relationships [\#266](https://github.com/schmittjoh/JMSSerializerBundle/issues/266)
- Symfony 2.1.7 composer error [\#265](https://github.com/schmittjoh/JMSSerializerBundle/issues/265)
- Incorrect installation procedure in documentation [\#258](https://github.com/schmittjoh/JMSSerializerBundle/issues/258)
- Strange caching-error [\#257](https://github.com/schmittjoh/JMSSerializerBundle/issues/257)
- Missing dependency when installing with composer [\#256](https://github.com/schmittjoh/JMSSerializerBundle/issues/256)
- Deserialization to ArrayCollection not working as expected [\#255](https://github.com/schmittjoh/JMSSerializerBundle/issues/255)
- Unsupported format doesn't throw exception anymore [\#254](https://github.com/schmittjoh/JMSSerializerBundle/issues/254)
- Doc Refactoring: Annotations must use the SerializerBundle namespace [\#250](https://github.com/schmittjoh/JMSSerializerBundle/issues/250)
- Problem with composer [\#249](https://github.com/schmittjoh/JMSSerializerBundle/issues/249)
- DateTimeHandler::serializeDateTimeToJson\(\) - Argument must be an instance of DateTime, null given [\#248](https://github.com/schmittjoh/JMSSerializerBundle/issues/248)
- add compatibility with Symfony2 serializer interface [\#244](https://github.com/schmittjoh/JMSSerializerBundle/issues/244)
- Adding new formats by aliassing existing  [\#241](https://github.com/schmittjoh/JMSSerializerBundle/issues/241)
- JMS\Parser\AbstractParser not find [\#237](https://github.com/schmittjoh/JMSSerializerBundle/issues/237)
- During TypeScanning Never Load From Database [\#228](https://github.com/schmittjoh/JMSSerializerBundle/issues/228)
- Traversable objects serialized as objects, not as arrays [\#224](https://github.com/schmittjoh/JMSSerializerBundle/issues/224)
- Lazy loader and setSerializeNull exception [\#207](https://github.com/schmittjoh/JMSSerializerBundle/issues/207)
- i18n-ize the DateTimeHandler using IntlDateFormatter [\#147](https://github.com/schmittjoh/JMSSerializerBundle/issues/147)
- Add a new AccessType to allow for \_\_call\(\) [\#104](https://github.com/schmittjoh/JMSSerializerBundle/issues/104)
- AccessType - Not working for unserialization [\#89](https://github.com/schmittjoh/JMSSerializerBundle/issues/89)
- add a depth exclusion strategy [\#61](https://github.com/schmittjoh/JMSSerializerBundle/issues/61)

**Merged pull requests:**

- Fixed messages of exceptions in CustomHandlersPass [\#267](https://github.com/schmittjoh/JMSSerializerBundle/pull/267) ([yethee](https://github.com/yethee))
- \[Easy-pick\] Fixed a few typos in the documentation [\#261](https://github.com/schmittjoh/JMSSerializerBundle/pull/261) ([csarrazi](https://github.com/csarrazi))
- renamed DateTimeHandler to new DateHandler [\#252](https://github.com/schmittjoh/JMSSerializerBundle/pull/252) ([simonchrz](https://github.com/simonchrz))
- add the fact that there is a 0.10 release [\#245](https://github.com/schmittjoh/JMSSerializerBundle/pull/245) ([lsmith77](https://github.com/lsmith77))
- PHP templating helper. [\#208](https://github.com/schmittjoh/JMSSerializerBundle/pull/208) ([rafalwrzeszcz](https://github.com/rafalwrzeszcz))

## [0.10](https://github.com/schmittjoh/JMSSerializerBundle/tree/0.10) (2012-11-17)

**Closed issues:**

- xml\_root\_name not working [\#227](https://github.com/schmittjoh/JMSSerializerBundle/issues/227)
- Error in serialization [\#212](https://github.com/schmittjoh/JMSSerializerBundle/issues/212)
- Strange behavior with groups : relation is no more serialized [\#200](https://github.com/schmittjoh/JMSSerializerBundle/issues/200)
- Cannot guess property of type array\<subType\> [\#199](https://github.com/schmittjoh/JMSSerializerBundle/issues/199)
- \[RuntimeException\]  on composer update [\#193](https://github.com/schmittjoh/JMSSerializerBundle/issues/193)
- Arrays keys are lost on XML [\#192](https://github.com/schmittjoh/JMSSerializerBundle/issues/192)
- RFC: Refactoring Custom Handlers [\#190](https://github.com/schmittjoh/JMSSerializerBundle/issues/190)
- Relation OneToMany, How can I exclude the fields of the relationship? [\#184](https://github.com/schmittjoh/JMSSerializerBundle/issues/184)
- memory exhausted when serializing [\#182](https://github.com/schmittjoh/JMSSerializerBundle/issues/182)
- \[LogicException\] Container extension "jms\_serializer" is not registered [\#181](https://github.com/schmittjoh/JMSSerializerBundle/issues/181)
- Implementations of ArrayAccess always serialize to array \(ignore field annotation\) [\#179](https://github.com/schmittjoh/JMSSerializerBundle/issues/179)
- Configured order of custom handlers is ignored [\#174](https://github.com/schmittjoh/JMSSerializerBundle/issues/174)
- Tagged releases [\#171](https://github.com/schmittjoh/JMSSerializerBundle/issues/171)
- Incorrect datetime format in documentation [\#153](https://github.com/schmittjoh/JMSSerializerBundle/issues/153)
- Add an ObjectConstructor which can load an object from the database [\#141](https://github.com/schmittjoh/JMSSerializerBundle/issues/141)
- Cannot handle both Date and DateTime [\#134](https://github.com/schmittjoh/JMSSerializerBundle/issues/134)
- Collection Objects for Xml Serialization [\#124](https://github.com/schmittjoh/JMSSerializerBundle/issues/124)
- Serializing a collection of mixed objects [\#117](https://github.com/schmittjoh/JMSSerializerBundle/issues/117)
- add support for to force json hash maps to numerically ordered arrays [\#57](https://github.com/schmittjoh/JMSSerializerBundle/issues/57)
- "PropertyName" Naming Strategy [\#49](https://github.com/schmittjoh/JMSSerializerBundle/issues/49)
- \[Enhancement\] Serializing using JSON referencing for referenced objects [\#32](https://github.com/schmittjoh/JMSSerializerBundle/issues/32)

**Merged pull requests:**

- Changes from Automatic Review [\#232](https://github.com/schmittjoh/JMSSerializerBundle/pull/232) ([JMSBot](https://github.com/JMSBot))
- Changes from Automatic Review [\#231](https://github.com/schmittjoh/JMSSerializerBundle/pull/231) ([JMSBot](https://github.com/JMSBot))
- Detect malformed group names [\#229](https://github.com/schmittjoh/JMSSerializerBundle/pull/229) ([Seldaek](https://github.com/Seldaek))
- fix doctrine ODM persistent collections [\#226](https://github.com/schmittjoh/JMSSerializerBundle/pull/226) ([MDrollette](https://github.com/MDrollette))
- Changes from Automatic Review [\#220](https://github.com/schmittjoh/JMSSerializerBundle/pull/220) ([JMSBot](https://github.com/JMSBot))
- Fix doctrine persistent collections not serializing [\#219](https://github.com/schmittjoh/JMSSerializerBundle/pull/219) ([baldurrensch](https://github.com/baldurrensch))
- Changes from Automatic Review [\#214](https://github.com/schmittjoh/JMSSerializerBundle/pull/214) ([JMSBot](https://github.com/JMSBot))
- fix lazy loader setSerializeNull [\#211](https://github.com/schmittjoh/JMSSerializerBundle/pull/211) ([hashnz](https://github.com/hashnz))
- Re enable ConstraintViolationHandler [\#203](https://github.com/schmittjoh/JMSSerializerBundle/pull/203) ([rpg600](https://github.com/rpg600))
- Re enable the FormErrorHandler [\#202](https://github.com/schmittjoh/JMSSerializerBundle/pull/202) ([adrienbrault](https://github.com/adrienbrault))
- Allow access to Serializer's visitors getter [\#201](https://github.com/schmittjoh/JMSSerializerBundle/pull/201) ([adrienbrault](https://github.com/adrienbrault))
- PHP 5.3.6 Bug Resolved [\#198](https://github.com/schmittjoh/JMSSerializerBundle/pull/198) ([emgiezet](https://github.com/emgiezet))
- Array typed as hashes \(array\<X,X\>\), are now correctly serialized when empty [\#197](https://github.com/schmittjoh/JMSSerializerBundle/pull/197) ([adrienbrault](https://github.com/adrienbrault))
- Fix RegisterEventListenersAndSubscribersPass & event\_subscribers service accessibility [\#196](https://github.com/schmittjoh/JMSSerializerBundle/pull/196) ([adrienbrault](https://github.com/adrienbrault))
- Update Resources/doc/handlers.rst [\#195](https://github.com/schmittjoh/JMSSerializerBundle/pull/195) ([adrienbrault](https://github.com/adrienbrault))
- Services tagged with "jms\_serializer.subscribing\_handler" should be public [\#194](https://github.com/schmittjoh/JMSSerializerBundle/pull/194) ([adrienbrault](https://github.com/adrienbrault))
- \[WIP\] adds an event system [\#189](https://github.com/schmittjoh/JMSSerializerBundle/pull/189) ([schmittjoh](https://github.com/schmittjoh))
- Pass object to each exclusion strategy class, take two [\#187](https://github.com/schmittjoh/JMSSerializerBundle/pull/187) ([Seldaek](https://github.com/Seldaek))
- Added DoctrineObjectConstructor [\#185](https://github.com/schmittjoh/JMSSerializerBundle/pull/185) ([guilhermeblanco](https://github.com/guilhermeblanco))
- Support whitelist for xml document types [\#183](https://github.com/schmittjoh/JMSSerializerBundle/pull/183) ([michelsalib](https://github.com/michelsalib))
- Nullable [\#177](https://github.com/schmittjoh/JMSSerializerBundle/pull/177) ([hashnz](https://github.com/hashnz))
- Fix deserialization with custom handlers [\#169](https://github.com/schmittjoh/JMSSerializerBundle/pull/169) ([eugene-dounar](https://github.com/eugene-dounar))
- Add an XmlAttributeMap [\#164](https://github.com/schmittjoh/JMSSerializerBundle/pull/164) ([adrienbrault](https://github.com/adrienbrault))
- Fixed missing support for Accessor in YamlDriver [\#156](https://github.com/schmittjoh/JMSSerializerBundle/pull/156) ([wheelsandcogs](https://github.com/wheelsandcogs))
- readonly flag should be set before setter, because the setter depends on that being set [\#154](https://github.com/schmittjoh/JMSSerializerBundle/pull/154) ([mvrhov](https://github.com/mvrhov))

## [0.9.0](https://github.com/schmittjoh/JMSSerializerBundle/tree/0.9.0) (2012-09-20)

**Closed issues:**

- @XMLList ignores child @XMLRoot and @XMLAttribute [\#176](https://github.com/schmittjoh/JMSSerializerBundle/issues/176)
- How to exclude fields from the associated objects? [\#173](https://github.com/schmittjoh/JMSSerializerBundle/issues/173)
- Inheritance issue [\#167](https://github.com/schmittjoh/JMSSerializerBundle/issues/167)
- Cannot deserialize attributes that are camelCased [\#166](https://github.com/schmittjoh/JMSSerializerBundle/issues/166)
- Passing arguments to an accessor method [\#165](https://github.com/schmittjoh/JMSSerializerBundle/issues/165)
- Cant update with composer [\#162](https://github.com/schmittjoh/JMSSerializerBundle/issues/162)
- Support for limiting nesting level [\#161](https://github.com/schmittjoh/JMSSerializerBundle/issues/161)
- Default to @ExclusionPolicy\("ALL"\) instead of "NONE" [\#159](https://github.com/schmittjoh/JMSSerializerBundle/issues/159)
- YAML Reference doesn't have an example for "accessor" [\#148](https://github.com/schmittjoh/JMSSerializerBundle/issues/148)
- No way to specify accessor\_setter using YAML [\#146](https://github.com/schmittjoh/JMSSerializerBundle/issues/146)
- @VirtualProperty methods are not exposed when used with @ExclusionPolicy\("ALL"\) [\#137](https://github.com/schmittjoh/JMSSerializerBundle/issues/137)
- Unexpected results serializing classes implementing Interator\(Aggregate\) [\#136](https://github.com/schmittjoh/JMSSerializerBundle/issues/136)
- Handle Type names case-insenstive [\#133](https://github.com/schmittjoh/JMSSerializerBundle/issues/133)
- Add support for Doctrine Annotations [\#131](https://github.com/schmittjoh/JMSSerializerBundle/issues/131)
- JMSSerializerBundle without symfony ? [\#128](https://github.com/schmittjoh/JMSSerializerBundle/issues/128)
- Entity serialization issue [\#126](https://github.com/schmittjoh/JMSSerializerBundle/issues/126)
- Fatal error: Cannot redeclare class Doctrine\ORM\Mapping\Annotation in vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Annotation.php on line 22 [\#123](https://github.com/schmittjoh/JMSSerializerBundle/issues/123)
- joinTable error with a single table inheritance join [\#119](https://github.com/schmittjoh/JMSSerializerBundle/issues/119)
- Persisting MongoDB Documents with ReferenceOne after deserialization [\#118](https://github.com/schmittjoh/JMSSerializerBundle/issues/118)
- DateTime deserialization + Accessor\(setter\) bug? [\#115](https://github.com/schmittjoh/JMSSerializerBundle/issues/115)
- Partial Object Support [\#114](https://github.com/schmittjoh/JMSSerializerBundle/issues/114)
- Tests failing for AccessorOrder [\#113](https://github.com/schmittjoh/JMSSerializerBundle/issues/113)
- Fatal error when a serialized object contains a resource [\#112](https://github.com/schmittjoh/JMSSerializerBundle/issues/112)
- Notice: Undefined offset: 15 in /vendor/bundles/JMS/SerializerBundle/Metadata/PropertyMetadata.php line 113 [\#110](https://github.com/schmittjoh/JMSSerializerBundle/issues/110)
- Fatal error [\#108](https://github.com/schmittjoh/JMSSerializerBundle/issues/108)
- Serialization Groups Improvements [\#107](https://github.com/schmittjoh/JMSSerializerBundle/issues/107)
- Serialization to native PHP? [\#106](https://github.com/schmittjoh/JMSSerializerBundle/issues/106)
- make it possible to combine exclusion strategies [\#105](https://github.com/schmittjoh/JMSSerializerBundle/issues/105)
- Make use of Doctrine annotations [\#103](https://github.com/schmittjoh/JMSSerializerBundle/issues/103)
- Undefined offset: 14 in JMS/SerializerBundle/Metadata/PropertyMetadata.php line 110 [\#97](https://github.com/schmittjoh/JMSSerializerBundle/issues/97)
- Undefined index error [\#95](https://github.com/schmittjoh/JMSSerializerBundle/issues/95)
- Use array keys as xml tag [\#92](https://github.com/schmittjoh/JMSSerializerBundle/issues/92)
- Customized attributes in JSON [\#91](https://github.com/schmittjoh/JMSSerializerBundle/issues/91)
- Allow for custom metadata [\#87](https://github.com/schmittjoh/JMSSerializerBundle/issues/87)
- Notice generated in PropertyMetadata, causes PHPUnit to throw Notice exception [\#86](https://github.com/schmittjoh/JMSSerializerBundle/issues/86)
- \[Enhancement\] Properties without setter are read-only [\#85](https://github.com/schmittjoh/JMSSerializerBundle/issues/85)
- ArrayNodeDefinition::defaultValue\(\) [\#83](https://github.com/schmittjoh/JMSSerializerBundle/issues/83)
- Serialize to array [\#82](https://github.com/schmittjoh/JMSSerializerBundle/issues/82)
- Change state inside getter [\#80](https://github.com/schmittjoh/JMSSerializerBundle/issues/80)
- Disable serializer for FOSUserBundle entities [\#78](https://github.com/schmittjoh/JMSSerializerBundle/issues/78)
- Readme fix [\#77](https://github.com/schmittjoh/JMSSerializerBundle/issues/77)
- Error in circular references \(function nesting level of '100' reached\) [\#72](https://github.com/schmittjoh/JMSSerializerBundle/issues/72)
- Documentation missing vital step [\#71](https://github.com/schmittjoh/JMSSerializerBundle/issues/71)
- Metadata definition only in last class hiearchy. [\#69](https://github.com/schmittjoh/JMSSerializerBundle/issues/69)
- Serialization groups [\#60](https://github.com/schmittjoh/JMSSerializerBundle/issues/60)
- Support for serializing arrays to XML [\#59](https://github.com/schmittjoh/JMSSerializerBundle/issues/59)
- make it possible to force numeric indexing in XmlCollection [\#56](https://github.com/schmittjoh/JMSSerializerBundle/issues/56)
- Class 'Metadata\Driver\LazyLoadingDriver' not found [\#55](https://github.com/schmittjoh/JMSSerializerBundle/issues/55)
- The annotation \"@proxy\" was never imported [\#51](https://github.com/schmittjoh/JMSSerializerBundle/issues/51)
- Doctrine ODM proxies serialization and hooks [\#50](https://github.com/schmittjoh/JMSSerializerBundle/issues/50)
- serializing an array of objects using SerializationHandlerInterface [\#48](https://github.com/schmittjoh/JMSSerializerBundle/issues/48)
- problem with metadata bundle version [\#47](https://github.com/schmittjoh/JMSSerializerBundle/issues/47)
- Slow initialization when enabling the bundle [\#44](https://github.com/schmittjoh/JMSSerializerBundle/issues/44)
- Bug in boolean deserialization ? [\#43](https://github.com/schmittjoh/JMSSerializerBundle/issues/43)
- Deserialization visitor is not using custom handlers [\#37](https://github.com/schmittjoh/JMSSerializerBundle/issues/37)
- Make it possible to deserialize content into property [\#35](https://github.com/schmittjoh/JMSSerializerBundle/issues/35)
- Doctrine Proxy Class Serialization [\#34](https://github.com/schmittjoh/JMSSerializerBundle/issues/34)
- PreSerialize not executing [\#31](https://github.com/schmittjoh/JMSSerializerBundle/issues/31)
- make it possible to control the order of the handlers [\#30](https://github.com/schmittjoh/JMSSerializerBundle/issues/30)
- Proxy class serialization [\#27](https://github.com/schmittjoh/JMSSerializerBundle/issues/27)
- Unable to use Resource identifier for directories path in confing.yml [\#26](https://github.com/schmittjoh/JMSSerializerBundle/issues/26)
- support NormalizeableInterface [\#21](https://github.com/schmittjoh/JMSSerializerBundle/issues/21)
- date\_default\_timezone\_get\(\) [\#14](https://github.com/schmittjoh/JMSSerializerBundle/issues/14)
- please add vendor prefix to repo name [\#11](https://github.com/schmittjoh/JMSSerializerBundle/issues/11)
- how to set a custom normalizer for a 3rd party class that matches the native normalizer [\#9](https://github.com/schmittjoh/JMSSerializerBundle/issues/9)
- tighter control over normalizer order [\#6](https://github.com/schmittjoh/JMSSerializerBundle/issues/6)
- add an AnnotatedNormalizer service [\#4](https://github.com/schmittjoh/JMSSerializerBundle/issues/4)

**Merged pull requests:**

- Add missing use. [\#175](https://github.com/schmittjoh/JMSSerializerBundle/pull/175) ([armetiz](https://github.com/armetiz))
- Options for json\_encode [\#151](https://github.com/schmittjoh/JMSSerializerBundle/pull/151) ([megazoll](https://github.com/megazoll))
- Always expose virtual properties by default [\#145](https://github.com/schmittjoh/JMSSerializerBundle/pull/145) ([Lumbendil](https://github.com/Lumbendil))
- Support for xmlKeyValuePairs in xml and yml metadata driver [\#143](https://github.com/schmittjoh/JMSSerializerBundle/pull/143) ([Spea](https://github.com/Spea))
- Do not ask for setter if the property is defined as @ReadOnly [\#138](https://github.com/schmittjoh/JMSSerializerBundle/pull/138) ([arghav](https://github.com/arghav))
- Added an upper bound on the composer constraint [\#130](https://github.com/schmittjoh/JMSSerializerBundle/pull/130) ([stof](https://github.com/stof))
- Added support for using the keys of an array as XML tag. [\#129](https://github.com/schmittjoh/JMSSerializerBundle/pull/129) ([Spea](https://github.com/Spea))
- fix xml reference [\#127](https://github.com/schmittjoh/JMSSerializerBundle/pull/127) ([gimler](https://github.com/gimler))
- Added fix for nested element with attribute and value [\#125](https://github.com/schmittjoh/JMSSerializerBundle/pull/125) ([matthiasnoback](https://github.com/matthiasnoback))
- Use composer to install development requirements [\#120](https://github.com/schmittjoh/JMSSerializerBundle/pull/120) ([mvrhov](https://github.com/mvrhov))
- Add @Virtual annotation [\#109](https://github.com/schmittjoh/JMSSerializerBundle/pull/109) ([anyx](https://github.com/anyx))
- Fixed typo on the composer installation instructions [\#99](https://github.com/schmittjoh/JMSSerializerBundle/pull/99) ([ruimarinho](https://github.com/ruimarinho))
- Add the Groups feature [\#96](https://github.com/schmittjoh/JMSSerializerBundle/pull/96) ([chregu](https://github.com/chregu))
- Fixed throwing exception in case of callback method doesn't exist for the yaml driver [\#94](https://github.com/schmittjoh/JMSSerializerBundle/pull/94) ([AlexKovalevych](https://github.com/AlexKovalevych))
- Support for @ReadOnly annotation [\#90](https://github.com/schmittjoh/JMSSerializerBundle/pull/90) ([ruudk](https://github.com/ruudk))
- renamed method to getConfiguration\(\) to support config:dump-reference [\#88](https://github.com/schmittjoh/JMSSerializerBundle/pull/88) ([lsmith77](https://github.com/lsmith77))
- \[Configuration\] Sync with upstream symfony2.1 [\#84](https://github.com/schmittjoh/JMSSerializerBundle/pull/84) ([helmer](https://github.com/helmer))
- added missed use statement [\#79](https://github.com/schmittjoh/JMSSerializerBundle/pull/79) ([cystbear](https://github.com/cystbear))
- Add documentation on how to override class metadata [\#76](https://github.com/schmittjoh/JMSSerializerBundle/pull/76) ([mvrhov](https://github.com/mvrhov))
- Inline support for properties [\#75](https://github.com/schmittjoh/JMSSerializerBundle/pull/75) ([mvrhov](https://github.com/mvrhov))
- Make tests run when doctrine \>= 2.2 [\#74](https://github.com/schmittjoh/JMSSerializerBundle/pull/74) ([mvrhov](https://github.com/mvrhov))
- Extend accessor setting for XML driver [\#73](https://github.com/schmittjoh/JMSSerializerBundle/pull/73) ([yethee](https://github.com/yethee))
- Add forgotten sprintf support for thrown exceptions in XmlSerializationV... [\#68](https://github.com/schmittjoh/JMSSerializerBundle/pull/68) ([richardfullmer](https://github.com/richardfullmer))
- Removed backup files [\#67](https://github.com/schmittjoh/JMSSerializerBundle/pull/67) ([helmer](https://github.com/helmer))
- Case insensitivity for boolean and exclusion policy [\#66](https://github.com/schmittjoh/JMSSerializerBundle/pull/66) ([mvrhov](https://github.com/mvrhov))
- Changed xml and yml drivers in such way that only excluded or exposed [\#65](https://github.com/schmittjoh/JMSSerializerBundle/pull/65) ([mvrhov](https://github.com/mvrhov))
- update datetime part of configuration to match the code [\#64](https://github.com/schmittjoh/JMSSerializerBundle/pull/64) ([mvrhov](https://github.com/mvrhov))
- Update composer.json, BC with the metadata library [\#63](https://github.com/schmittjoh/JMSSerializerBundle/pull/63) ([yethee](https://github.com/yethee))
- Twig filter access to serializer [\#62](https://github.com/schmittjoh/JMSSerializerBundle/pull/62) ([jonotron](https://github.com/jonotron))
- made the handling of Doctrine proxies generic [\#58](https://github.com/schmittjoh/JMSSerializerBundle/pull/58) ([lsmith77](https://github.com/lsmith77))
- Added a missing convertion the SimpleXMLElement to a string. [\#53](https://github.com/schmittjoh/JMSSerializerBundle/pull/53) ([yethee](https://github.com/yethee))
- Fix undefined variable [\#52](https://github.com/schmittjoh/JMSSerializerBundle/pull/52) ([yethee](https://github.com/yethee))
- Implement the handler of serialization to process the proxy of Doctrine ORM [\#46](https://github.com/schmittjoh/JMSSerializerBundle/pull/46) ([yethee](https://github.com/yethee))
- add composer.json [\#45](https://github.com/schmittjoh/JMSSerializerBundle/pull/45) ([igorw](https://github.com/igorw))
- added a note on how to control the handler order [\#42](https://github.com/schmittjoh/JMSSerializerBundle/pull/42) ([lsmith77](https://github.com/lsmith77))
- Changed installation documentation. [\#41](https://github.com/schmittjoh/JMSSerializerBundle/pull/41) ([michalpipa](https://github.com/michalpipa))
- Updated installation section of doc [\#40](https://github.com/schmittjoh/JMSSerializerBundle/pull/40) ([dustin10](https://github.com/dustin10))
- Corrected typo in custom handler service tag. [\#38](https://github.com/schmittjoh/JMSSerializerBundle/pull/38) ([michalpipa](https://github.com/michalpipa))
- Make it possible to deserialize content into property [\#36](https://github.com/schmittjoh/JMSSerializerBundle/pull/36) ([michelsalib](https://github.com/michelsalib))
- Fix docs [\#33](https://github.com/schmittjoh/JMSSerializerBundle/pull/33) ([cystbear](https://github.com/cystbear))
- cast XmlElement to string [\#29](https://github.com/schmittjoh/JMSSerializerBundle/pull/29) ([rande](https://github.com/rande))
- About issue \#26 [\#28](https://github.com/schmittjoh/JMSSerializerBundle/pull/28) ([ftassi](https://github.com/ftassi))
- typo fix in object\_based handler config [\#24](https://github.com/schmittjoh/JMSSerializerBundle/pull/24) ([lsmith77](https://github.com/lsmith77))
- check if the data implements SerializationHandlerInterface and in that ca [\#23](https://github.com/schmittjoh/JMSSerializerBundle/pull/23) ([lsmith77](https://github.com/lsmith77))
- Corrected github location of repo [\#22](https://github.com/schmittjoh/JMSSerializerBundle/pull/22) ([dsyph3r](https://github.com/dsyph3r))
- Updated the documentation [\#20](https://github.com/schmittjoh/JMSSerializerBundle/pull/20) ([yethee](https://github.com/yethee))
- Fixed missing `public` modificator of method [\#19](https://github.com/schmittjoh/JMSSerializerBundle/pull/19) ([yethee](https://github.com/yethee))
- Serialization handler for the constraint violations [\#18](https://github.com/schmittjoh/JMSSerializerBundle/pull/18) ([yethee](https://github.com/yethee))
- Added support serialization the nested form errors. [\#17](https://github.com/schmittjoh/JMSSerializerBundle/pull/17) ([yethee](https://github.com/yethee))
- Form error handler [\#16](https://github.com/schmittjoh/JMSSerializerBundle/pull/16) ([yethee](https://github.com/yethee))
- Fixed value of the parameter jms\_serializer.unserialize\_object\_constructor.class [\#15](https://github.com/schmittjoh/JMSSerializerBundle/pull/15) ([yethee](https://github.com/yethee))
- Fix libxml\_get\_last\_error\(\) string converting in xml driver and added mor [\#13](https://github.com/schmittjoh/JMSSerializerBundle/pull/13) ([patashnik](https://github.com/patashnik))
- Fixed typo [\#12](https://github.com/schmittjoh/JMSSerializerBundle/pull/12) ([yethee](https://github.com/yethee))
- put the custom normalizers first [\#10](https://github.com/schmittjoh/JMSSerializerBundle/pull/10) ([lsmith77](https://github.com/lsmith77))
- added priority support for normalizer order \(fix for \#6\) [\#8](https://github.com/schmittjoh/JMSSerializerBundle/pull/8) ([lsmith77](https://github.com/lsmith77))
- sync API with PR 832 [\#3](https://github.com/schmittjoh/JMSSerializerBundle/pull/3) ([lsmith77](https://github.com/lsmith77))



\* *This Change Log was automatically generated by [github_changelog_generator](https://github.com/skywinder/Github-Changelog-Generator)*
