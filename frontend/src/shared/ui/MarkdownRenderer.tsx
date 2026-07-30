/**
 * MarkdownRenderer — Rendu Markdown minimal côté client.
 *
 * Supporte les syntaxes utilisées dans les guides du Chemin :
 *   - Titres : # ## ###
 *   - Gras : **texte**
 *   - Italique : _texte_
 *   - Listes : - item  ou  * item
 *   - Lignes vides → paragraphes
 *   - Tableaux : | col | col | (ligne header + séparateur + données)
 *   - Liens : [texte](url)
 *
 * Approche ligne par ligne avec regex — pas de dépendance externe.
 * Sécurité : pas de dangerouslySetInnerHTML — tout est JSX.
 */

import React from 'react';

interface MarkdownRendererProps {
  content: string;
  className?: string;
}

// ─── Inline renderer ─────────────────────────────────────────────────────────

/** Transforme le texte inline (gras, italique, liens) en fragments React. */
function renderInline(text: string): React.ReactNode {
  // Pattern: **gras**, _italique_, [texte](url)
  const parts = text.split(/(\*\*[^*]+\*\*|_[^_]+_|\[[^\]]+\]\([^)]+\))/g);
  return parts.map((part, i) => {
    if (part.startsWith('**') && part.endsWith('**')) {
      return <strong key={i}>{part.slice(2, -2)}</strong>;
    }
    if (part.startsWith('_') && part.endsWith('_') && part.length > 2) {
      return <em key={i}>{part.slice(1, -1)}</em>;
    }
    const linkMatch = /^\[([^\]]+)\]\(([^)]+)\)$/.exec(part);
    if (linkMatch) {
      return (
        <a
          key={i}
          href={linkMatch[2]}
          target="_blank"
          rel="noopener noreferrer"
          style={{ color: 'var(--color-text-accent)', textDecoration: 'underline' }}
        >
          {linkMatch[1]}
        </a>
      );
    }
    return part;
  });
}

// ─── Block renderer ───────────────────────────────────────────────────────────

type Block =
  | { type: 'h1' | 'h2' | 'h3'; text: string }
  | { type: 'li'; text: string }
  | { type: 'p'; text: string }
  | { type: 'table'; rows: string[][] }
  | { type: 'blank' };

/** Détecte si une ligne est un séparateur de tableau Markdown (|---|---|). */
function isTableSeparator(line: string): boolean {
  return /^\|[\s\-:|]+\|/.test(line);
}

/** Parse les cellules d'une ligne de tableau Markdown. */
function parseTableRow(line: string): string[] {
  return line
    .split('|')
    .slice(1, -1)
    .map(cell => cell.trim());
}

/** Transforme le contenu Markdown en blocs structurés. */
function parseBlocks(content: string): Block[] {
  const lines = (content ?? '').split('\n');
  const blocks: Block[] = [];
  let i = 0;

  while (i < lines.length) {
    const line = lines[i] ?? '';

    // Titres
    if (line.startsWith('### ')) {
      blocks.push({ type: 'h3', text: line.slice(4) });
      i++;
      continue;
    }
    if (line.startsWith('## ')) {
      blocks.push({ type: 'h2', text: line.slice(3) });
      i++;
      continue;
    }
    if (line.startsWith('# ')) {
      blocks.push({ type: 'h1', text: line.slice(2) });
      i++;
      continue;
    }

    // Listes
    if (/^[-*] /.test(line)) {
      blocks.push({ type: 'li', text: line.slice(2) });
      i++;
      continue;
    }

    // Tableaux — détection header + séparateur
    if (line.startsWith('|') && i + 1 < lines.length && isTableSeparator(lines[i + 1] ?? '')) {
      const rows: string[][] = [];
      rows.push(parseTableRow(line));
      i += 2; // saute header + séparateur
      while (i < lines.length && (lines[i] ?? '').startsWith('|')) {
        rows.push(parseTableRow(lines[i] ?? ''));
        i++;
      }
      blocks.push({ type: 'table', rows });
      continue;
    }

    // Ligne vide
    if (line.trim() === '') {
      blocks.push({ type: 'blank' });
      i++;
      continue;
    }

    // Paragraphe par défaut
    blocks.push({ type: 'p', text: line });
    i++;
  }

  return blocks;
}

// ─── Regroupement des blocs ───────────────────────────────────────────────────

/** Regroupe les `li` consécutifs en listes `<ul>`. */
function groupBlocks(blocks: Block[]): React.ReactNode[] {
  const nodes: React.ReactNode[] = [];
  let key = 0;
  let i = 0;

  while (i < blocks.length) {
    const block = blocks[i];
    if (!block) { i++; continue; }

    if (block.type === 'li') {
      const items: string[] = [block.text];
      let j = i + 1;
      while (j < blocks.length && blocks[j]?.type === 'li') {
        items.push((blocks[j] as { type: 'li'; text: string }).text);
        j++;
      }
      nodes.push(
        <ul key={key++} style={{ paddingLeft: 'var(--space-5)', margin: 'var(--space-2) 0' }}>
          {items.map((item, idx) => (
            <li key={idx} style={{ marginBottom: 'var(--space-1)', lineHeight: 'var(--line-height-relaxed)' }}>
              {renderInline(item)}
            </li>
          ))}
        </ul>,
      );
      i = j;
      continue;
    }

    if (block.type === 'blank') {
      i++;
      continue;
    }

    if (block.type === 'h1') {
      nodes.push(
        <h1 key={key++} style={{
          fontSize: 'var(--font-size-xl)',
          fontWeight: 'var(--font-weight-bold)',
          color: 'var(--color-text-primary)',
          margin: 'var(--space-6) 0 var(--space-3)',
          fontFamily: 'var(--font-family-display)',
        }}>
          {renderInline(block.text)}
        </h1>,
      );
      i++;
      continue;
    }

    if (block.type === 'h2') {
      nodes.push(
        <h2 key={key++} style={{
          fontSize: 'var(--font-size-lg)',
          fontWeight: 'var(--font-weight-semibold)',
          color: 'var(--color-text-primary)',
          margin: 'var(--space-5) 0 var(--space-2)',
          fontFamily: 'var(--font-family-display)',
        }}>
          {renderInline(block.text)}
        </h2>,
      );
      i++;
      continue;
    }

    if (block.type === 'h3') {
      nodes.push(
        <h3 key={key++} style={{
          fontSize: 'var(--font-size-md)',
          fontWeight: 'var(--font-weight-semibold)',
          color: 'var(--color-text-secondary)',
          margin: 'var(--space-4) 0 var(--space-2)',
        }}>
          {renderInline(block.text)}
        </h3>,
      );
      i++;
      continue;
    }

    if (block.type === 'table') {
      const [header, ...dataRows] = block.rows;
      nodes.push(
        <div key={key++} style={{ overflowX: 'auto', margin: 'var(--space-3) 0' }}>
          <table style={{
            width: '100%',
            borderCollapse: 'collapse',
            fontSize: 'var(--font-size-sm)',
          }}>
            {header && (
              <thead>
                <tr>
                  {header.map((cell, ci) => (
                    <th key={ci} style={{
                      padding: 'var(--space-2) var(--space-3)',
                      textAlign: 'left',
                      borderBottom: '2px solid var(--color-border-subtle)',
                      fontWeight: 'var(--font-weight-semibold)',
                      color: 'var(--color-text-secondary)',
                    }}>
                      {renderInline(cell)}
                    </th>
                  ))}
                </tr>
              </thead>
            )}
            <tbody>
              {dataRows.map((row, ri) => (
                <tr key={ri} style={{
                  borderBottom: '1px solid var(--color-border-subtle)',
                }}>
                  {row.map((cell, ci) => (
                    <td key={ci} style={{
                      padding: 'var(--space-2) var(--space-3)',
                      color: 'var(--color-text-primary)',
                      verticalAlign: 'top',
                    }}>
                      {renderInline(cell)}
                    </td>
                  ))}
                </tr>
              ))}
            </tbody>
          </table>
        </div>,
      );
      i++;
      continue;
    }

    if (block.type === 'p') {
      nodes.push(
        <p key={key++} style={{
          margin: 'var(--space-3) 0',
          lineHeight: 'var(--line-height-relaxed)',
          color: 'var(--color-text-primary)',
          fontSize: 'var(--font-size-sm)',
        }}>
          {renderInline(block.text)}
        </p>,
      );
      i++;
      continue;
    }

    i++;
  }

  return nodes;
}

// ─── Composant public ─────────────────────────────────────────────────────────

export function MarkdownRenderer({ content, className }: MarkdownRendererProps) {
  const blocks = parseBlocks(content);
  const nodes = groupBlocks(blocks);

  return (
    <div
      className={className}
      style={{
        fontFamily: 'var(--font-family-interface)',
        color: 'var(--color-text-primary)',
      }}
    >
      {nodes}
    </div>
  );
}
