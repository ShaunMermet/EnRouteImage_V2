import matplotlib.pyplot as plt
import numpy as np
import sys
#import StringIO
import cv2

from skimage.data import astronaut
from skimage.color import rgb2gray
from skimage.filters import sobel
from skimage.segmentation import felzenszwalb, slic, quickshift, watershed
from skimage.segmentation import mark_boundaries
from skimage.util import img_as_float
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

#image = Image.open(filename)
image = cv2.imread(filename)
#image.show()
img=np.asarray(image)
#RGB_img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
#img = image

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


