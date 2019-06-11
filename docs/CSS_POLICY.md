# CSS Policy

We use the [GOVUK.UK Design System][govuk-ds] to provide as much as our styling as possible:

- To benefit from their accessibility, performance and usability concerns
- To be in-line with other GOV.UK services
- To reduce our maintenance requirements
- To be compliant with GDS standards

Where possible, **GOV.UK Design System components and patterns should be used**.

## Custom app CSS

Where the GOV.UK Design System does not meet our needs, we _may_ write custom CSS for the DigiDeps app. When doing so, the following rules should be followed:

- Reusable components should be designed as Twig templates or macros, so they can easily be updated in the future
- CSS should use classes, rather than IDs, attributes or tag selectors
- All custom CSS classes should be prefixed `app-`, to differentiate them from GOV.UK classes which are prefixed `govuk-`
- [Block, Element, Modifier (BEM)][bem] CSS methodology should be used
- Where suitable (e.g. typography, colours, spacing) [GOV.UK Sass variables][govuk-ds-variables] should be used

These rules keep our custom CSS close to the GOV.UK Design System, making code easier to review and maintain, and easing our route to new components when they are released.

[govuk-ds]: https://design-system.service.gov.uk/
[govuk-ds-variables]: https://github.com/alphagov/govuk-frontend/tree/master/src/settings
[bem]: https://css-tricks.com/bem-101/
