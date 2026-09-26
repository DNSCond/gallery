import requests

local = requests.get('http://localhost/gallery/get-all-img.php')
server = requests.get('https://antrequest.nl/gallery/get-all-img.php')

print('local :', local.status_code)
print('server:', server.status_code)

l = set(local.json()['images'])
s = set(server.json()['images'])

print('server but not local:')
print('   ', '\n    '.join(s - l))

print('local but not server:')
print('   ', '\n    '.join(l - s))

input('enter to exit:')