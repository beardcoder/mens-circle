/** @type {import('stylelint').Config} */
export default {
    plugins: ['stylelint-use-logical'],
    extends: ['stylelint-config-standard', 'stylelint-config-recess-order'],
    rules: {
        'csstools/use-logical': 'always',
        'selector-class-pattern': null,
        'no-descending-specificity': null,
    },
};
