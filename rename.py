import os
path = '.'
for filename in os.listdir(path):
    if 'tar.gz.' in filename:
        prefix, num = filename.split('.tar.gz.')
        num = num.zfill(3)
        new_filename = prefix + '.tar.gz.' +  num
        print new_filename
        os.rename(os.path.join(path, filename), os.path.join(path, new_filename))
