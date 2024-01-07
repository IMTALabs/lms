import { addClassToHast } from 'shikiji/core';

function parseHighlightLines(attrs) {
  if (!attrs)
    return null;
  const match = attrs.match(/{([\d,-]+)}/);
  if (!match)
    return null;
  const lines = match[1].split(",").flatMap((v) => {
    const num = v.split("-").map((v2) => Number.parseInt(v2, 10));
    if (num.length === 1)
      return [num[0]];
    else
      return Array.from({ length: num[1] - num[0] + 1 }, (_, i) => i + num[0]);
  });
  return lines;
}

function setupMarkdownIt(markdownit, highlighter, options) {
  const {
    highlightLines = true,
    parseMetaString
  } = options;
  markdownit.options.highlight = (code, lang = "text", attrs) => {
    const meta = parseMetaString?.(attrs, code, lang) || {};
    const codeOptions = {
      ...options,
      lang,
      meta: {
        ...options.meta,
        ...meta,
        __raw: attrs
      }
    };
    const builtInTransformer = [];
    if (highlightLines) {
      const lines = parseHighlightLines(attrs);
      if (lines) {
        const className = highlightLines === true ? "highlighted" : highlightLines;
        builtInTransformer.push({
          name: "markdown-it-shikiji:line-class",
          line(node, line) {
            if (lines.includes(line))
              addClassToHast(node, className);
            return node;
          }
        });
      }
    }
    builtInTransformer.push({
      name: "markdown-it-shikiji:block-class",
      code(node) {
        node.properties.class = `language-${lang}`;
      }
    });
    return highlighter.codeToHtml(
      code,
      {
        ...codeOptions,
        transformers: [
          ...builtInTransformer,
          ...codeOptions.transformers || []
        ]
      }
    );
  };
}
function fromHighlighter(highlighter, options) {
  return function(markdownit) {
    setupMarkdownIt(markdownit, highlighter, options);
  };
}

export { fromHighlighter, setupMarkdownIt };
