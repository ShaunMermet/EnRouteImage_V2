import matplotlib.pyplot as plt
import numpy as np
import sys
#import StringIO
import cv2

from skimage.segmentation import slic
from skimage.segmentation import mark_boundaries
from PIL import Image


#print('Argument List:', str(sys.argv))
#print(sys.argv[1])
filename = sys.argv[1]
nbrSegments = int(sys.argv[2])
compactness = float(sys.argv[3])
lastpath = filename.split("/")
name,ext = lastpath[-1].split(".")
#print(name)
#print(ext)

f1=open('./efs/tmp/testfile.txt', 'w+')
f1.write(filename+'\n')
f1.write(str(nbrSegments)+'\n')
f1.write(str(compactness)+'\n')


#image = Image.open(filename)
image = cv2.imread(filename,1)

#image.show()
img=np.asarray(image)
#RGB_img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
#img = image
check = img.tolist()
segments_slic = slic(img, n_segments=nbrSegments, compactness=compactness, sigma=1)
#print('SLIC number of segments: {}'.format(len(np.unique(segments_slic))))
print('{}'.format(len(np.unique(segments_slic))))
#print(segments_slic.shape)
y, x = segments_slic.shape
print(y)
print(x)
#nplist = np.arange(100000000)
#npstring = np.array2string(nplist, formatter={'int'})
#str1 = ''.join(segments_slic)
#print(npstring)
### GET STRING #####
nplist = segments_slic.tolist()
#npstring = np.array_str(nplist)


### APPLI OUT ###
print(nplist)

##### FILE SAVE ##### 
#np.savetxt(name+".txt", segments_slic,fmt="%i")
#print("created file : "+name+".txt")

##### PLOT #### 
#plt.imshow(mark_boundaries(RGB_img, segments_slic))
#plt.show()
f1.write(str(check)+'\n')
f1.write(str(nplist)+'\n')
f1.close()

