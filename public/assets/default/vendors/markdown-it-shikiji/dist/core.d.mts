import MarkdownIt from 'markdown-it';
import { CodeOptionsThemes, BuiltinTheme, TransformerOptions, CodeOptionsMeta, HighlighterGeneric } from 'shikiji';

interface MarkdownItShikijiExtraOptions {
    /**
     * Add `highlighted` class to lines defined in after codeblock
     *
     * @default true
     */
    highlightLines?: boolean | string;
    /**
     * Custom meta string parser
     * Return an object to merge with `meta`
     */
    parseMetaString?: (metaString: string, code: string, lang: string) => Record<string, any> | undefined | null;
}
type MarkdownItShikijiSetupOptions = CodeOptionsThemes<BuiltinTheme> & TransformerOptions & CodeOptionsMeta & MarkdownItShikijiExtraOptions;
declare function setupMarkdownIt(markdownit: MarkdownIt, highlighter: HighlighterGeneric<any, any>, options: MarkdownItShikijiSetupOptions): void;
declare function fromHighlighter(highlighter: HighlighterGeneric<any, any>, options: MarkdownItShikijiSetupOptions): (markdownit: MarkdownIt) => void;

export { type MarkdownItShikijiExtraOptions, type MarkdownItShikijiSetupOptions, fromHighlighter, setupMarkdownIt };
