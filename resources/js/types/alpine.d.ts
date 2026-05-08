declare module 'alpinejs' {
  interface Alpine {
    plugin(plugin: (alpine: Alpine) => void): void;
    start(): void;
  }

  const Alpine: Alpine;
  export default Alpine;
}

declare module '@alpinejs/collapse' {
  function collapse(alpine: unknown): void;
  export default collapse;
}
