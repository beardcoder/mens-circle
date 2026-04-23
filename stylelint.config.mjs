/** @type {import('stylelint').Config} */
export default {
  plugins: ['stylelint-use-logical'],
  extends: ['stylelint-config-standard', 'stylelint-config-recess-order'],
  rules: {
    'csstools/use-logical': 'always',
    'selector-class-pattern': null,
    'no-descending-specificity': null,
    // Allow `--_name` prefix for component-private custom properties
    // (css.dev convention) alongside standard kebab-case tokens.
    'custom-property-pattern': [
      '^_?[a-z][a-z0-9]*(-[a-z0-9]+)*$',
      {
        message:
          'Custom properties should be kebab-case; component-private may start with `--_`.',
      },
    ],
  },
};
