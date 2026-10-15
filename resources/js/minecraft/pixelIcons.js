/**
 * Small pixel-art icons for the Minecraft tools, drawn for this project
 * (no Mojang textures). Each icon is a list of equal-length rows; every
 * character is a palette colour, '.' is transparent.
 */

const PALETTE = {
    k: '#1f1c1a', // outline
    d: '#4a4a4f', // dark iron
    g: '#8b8b90', // iron
    l: '#c9c9cf', // light iron
    w: '#eef2f5', // highlight
    c: '#4fd4e0', // diamond
    C: '#2a8f9c', // dark diamond
    b: '#7a5230', // wood
    B: '#4e3219', // dark wood
    y: '#e8c04a', // gold
    Y: '#a9822a', // dark gold
    p: '#8f4fc4', // book cover
    P: '#5d2e86', // dark book cover
    s: '#e9e1cf', // paper
    r: '#c0392b', // red
    t: '#d6d0c4', // string
};

export const BOOK_ICON = [
    '................',
    '..kkkkkkkkkkk...',
    '.kppppppppppPk..',
    '.kpyyyyyyyypPk..',
    '.kppppppppppPk..',
    '.kpppyyyypppPk..',
    '.kppyppppyppPk..',
    '.kpppyyyypppPk..',
    '.kppppppppppPk..',
    '.kppppppppppPk..',
    '.kpyyyyyyyypPk..',
    '.kPPPPPPPPPPPk..',
    '.ksssssssssssk..',
    '..kkkkkkkkkkkk..',
    '................',
    '................',
];

const SWORD_ICON = [
    '.............kk.',
    '............kwlk',
    '...........kwlk.',
    '..........kwlk..',
    '.........kwlk...',
    '........kwlk....',
    '.......kwlk.....',
    '..kk..kwlk......',
    '..kyk.klk.......',
    '...kykdk........',
    '....kyk.........',
    '...kBkyk........',
    '..kBk.kyk.......',
    '.kbk...kk.......',
    '.kk.............',
    '................',
];

const PICKAXE_ICON = [
    '................',
    '....kkkkkkk.....',
    '...kllllllgk....',
    '..kgk...kkdgk...',
    '..kk....kbk.kk..',
    '.......kbk......',
    '......kbk.......',
    '.....kbk........',
    '....kbk.........',
    '...kbk..........',
    '..kbk...........',
    '.kBk............',
    '.kk.............',
    '................',
    '................',
    '................',
];

const ARMOR_ICON = [
    '................',
    '..kkkk....kkkk..',
    '.kllgkkkkkkglk..',
    '.klggggggggggk..',
    '.kkkgggggggkkk..',
    '...kggggggggk...',
    '...kglgggglgk...',
    '...kggggggggk...',
    '...kgggggggdk...',
    '...kgggggggdk...',
    '...kkkkkkkkkk...',
    '................',
    '................',
    '................',
    '................',
    '................',
];

const BOW_ICON = [
    '................',
    '..........kkk...',
    '........kkbbk...',
    '.......kbbkt....',
    '......kbk..t....',
    '.....kbk...t....',
    '....kbk....t....',
    '...kbk.....t....',
    '...kbk.....t....',
    '...kbk....t.....',
    '....kbk..t......',
    '.....kbkt.......',
    '......kbkk......',
    '.......kk.......',
    '................',
    '................',
];

const GEM_ICON = [
    '................',
    '................',
    '.....kkkkkk.....',
    '....kwccccCk....',
    '...kwccccccCk...',
    '...kccccccccCk..',
    '...kCcccccccCk..',
    '....kCccccCCk...',
    '.....kCccCCk....',
    '......kCCCk.....',
    '.......kkk......',
    '................',
    '................',
    '................',
    '................',
    '................',
];

const HEAD_ICON = [
    '................',
    '................',
    '...kkkkkkkkkk...',
    '...kggggggggk...',
    '...kggggggggk...',
    '...kgkkggkkgk...',
    '...kgkkggkkgk...',
    '...kggggggggk...',
    '...kggkkkkggk...',
    '...kggggggggk...',
    '...kkkkkkkkkk...',
    '................',
    '................',
    '................',
    '................',
    '................',
];

/** Decorative anvil for the anvil GUI title bar. */
export const ANVIL_GLYPH = [
    '................',
    '................',
    '.kkkkkkkkkkkkkk.',
    'kllllllllllllgk.',
    '.kggggggggggddk.',
    '..kkkkggggdkkk..',
    '.....kggggk.....',
    '.....kggddk.....',
    '....kkggggkk....',
    '...kggggggggk...',
    '...kddddddddk...',
    '...kkkkkkkkkk...',
    '................',
    '................',
    '................',
    '................',
];

const ICONS_BY_CATEGORY = {
    'Swords': SWORD_ICON,
    'Spears': SWORD_ICON,
    'Melee weapons': SWORD_ICON,
    'Axes': PICKAXE_ICON,
    'Pickaxes': PICKAXE_ICON,
    'Shovels': PICKAXE_ICON,
    'Hoes': PICKAXE_ICON,
    'Tools': PICKAXE_ICON,
    'Helmets': ARMOR_ICON,
    'Chestplates': ARMOR_ICON,
    'Leggings': ARMOR_ICON,
    'Boots': ARMOR_ICON,
    'Equipment': ARMOR_ICON,
    'Ranged weapons': BOW_ICON,
    'Heads': HEAD_ICON,
};

export function iconForItem(item) {
    return ICONS_BY_CATEGORY[item.category] || GEM_ICON;
}

const cache = new Map();

/**
 * Render an icon as an inline SVG string (one rect per horizontal run of a colour).
 */
export function pixelIcon(rows) {
    if (cache.has(rows))
        return cache.get(rows);

    const width = rows[0].length;
    const height = rows.length;
    let rects = '';
    rows.forEach((row, y) => {
        let x = 0;
        while (x < width) {
            const colour = PALETTE[row[x]];
            let run = 1;
            while (x + run < width && row[x + run] === row[x])
                run++;
            if (colour)
                rects += `<rect x="${x}" y="${y}" width="${run}" height="1" fill="${colour}"/>`;
            x += run;
        }
    });

    const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${width} ${height}" shape-rendering="crispEdges" aria-hidden="true" style="width:100%;height:100%;display:block">${rects}</svg>`;
    cache.set(rows, svg);
    return svg;
}
