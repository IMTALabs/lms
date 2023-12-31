import { getHighlighter, bundledLanguages } from 'shikiji';
import { setupMarkdownIt } from './core.mjs';
export { fromHighlighter } from './core.mjs';
import 'shikiji/core';

async function markdownItShikiji(options) {
  const themeNames = ("themes" in options ? Object.values(options.themes) : [options.theme]).filter(Boolean);
  const highlighter = await getHighlighter({
    themes: themeNames,
    langs: options.langs || Object.keys(bundledLanguages)
  });
  return function(markdownit) {
    setupMarkdownIt(markdownit, highlighter, options);
  };
}

export { markdownItShikiji as default, setupMarkdownIt };
