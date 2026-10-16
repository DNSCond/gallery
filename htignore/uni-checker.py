import json

with open('../imgdata/.assets.json', 'rt', encoding='utf8') as jsonin:
    data = json.load(jsonin)["chardata"]
with open('../characters.html', 'wt') as htmlout:
    htmlout.write('<!DOCTYPE html><html lang=en><meta charset=UTF-8><meta name=viewport content=\'width=device-width'
                  ',initial-scale=1\'><style>html{font-family:monospace;}.eTrue{background-color:mediumseagreen}'
                  '.eFalse{background-color:tomato}</style><link rel=stylesheet href=ddDL-table.css><meta name='
                  'robots content=noindex><body><table><thead><tr><th scope=row>Character<th scope=row>Universe'
                  ' Map<th scope=row>Universe Json<tbody>')
    for uni, univdata in data.items():
        for chardata in univdata.values():
            htmlout.write(f'<tr><td>{chardata['main.json']['name']} ({chardata['charId']})')
            htmlout.write(f'<td>{uni}<td>{chardata['main.json']['UniverseId']}')
pass
