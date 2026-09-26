from makeready import main as mkready
import paramiko, json, os

mkready()
path = "../imgdata"  # Current directory

# Get all files and directories
items = os.listdir(path)

# Filter out directories to get only files
local_files = [item for item in items if os.path.isfile(os.path.join(path, item))]

with open('credentails.json', 'rt', encoding='utf8') as file:
    data = json.load(file)

# Server connection details
host = data['host']
port = 22
username = data['user']
password = data['pass']  # Or use a private key file

# 1. Establish SSH connection
transport = paramiko.Transport((host, port))
transport.connect(username=username, password=password)

# 2. Start SFTP client
sftp = paramiko.SFTPClient.from_transport(transport)

# List files in a directory
server_files = sftp.listdir("/antrequest.nl/gallery/imgdata")

s = set(server_files)
l = set(local_files)

# print('server but not local:')
# print('   ', '\n    '.join(s - l))
step = int()
added = int()
deleted = int()
for step, file_name in enumerate(l - s):
    print(step, 'added.')
    sftp.put(f"../imgdata/{file_name}", f'/antrequest.nl/gallery/imgdata/{file_name}')
    added = step
for step, file_name in enumerate(s - l):
    print(step, 'deleted.')
    remote_path = f'/antrequest.nl/gallery/imgdata/{file_name}';
    try:
        sftp.remove(remote_path)
    except FileNotFoundError:
        pass
    deleted = step
# 4. Close the session
sftp.close()
transport.close()
print('---')
print(added, 'added.', deleted, 'deleted.')
input('enter to exit:')
