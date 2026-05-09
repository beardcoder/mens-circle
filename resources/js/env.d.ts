interface ImportMetaEnv {
  readonly PROD: boolean;
  readonly DEV: boolean;
  readonly SSR: boolean;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}

declare module '*.css' {
  const content: string;

  export default content;
}

declare module '@alpinejs/collapse';
declare module '@alpinejs/intersect';
