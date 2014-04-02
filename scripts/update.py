print("\n    _____                    _____ _        _      \n\
   |  __ \                  / ____| |      | |      \n\
   | |__) |__ _ __  _ __   | (___ | |_ __ _| |_ ___ \n\
   |  ___/ _ \ '_ \| '_ \   \___ \| __/ _` | __/ _ \ \n\
   | |  |  __/ | | | | | |  ____) | || (_| | ||  __/\n\
   |_|   \___|_| |_|_| |_| |_____/ \__\__,_|\__\___|\n\
   | |    (_)                | |  | |               \n\
   | |     ___   _____  ___  | |__| | ___ _ __ ___  \n\
   | |    | \ \ / / _ \/ __| |  __  |/ _ \ '__/ _ \ \n\
   | |____| |\ V /  __/\__ \ | |  | |  __/ | |  __/ \n\
   |______|_| \_/ \___||___/ |_|  |_|\___|_|  \___| \n\
                                                  \n\
                                                  ");
print("Running Penn State Classroom update................\n");

import pymongo
import urllib2
import linecache
from bson.objectid import ObjectId

import requests

import re

connection = pymongo.Connection()

db = connection["ist"]
classrooms = db["classrooms"]

cursor = db.classrooms.find()

# get classroom name

days = []

for classroom in cursor:
	url = "https://clc.its.psu.edu/labhours/RoomPrintout.aspx?&room=" + str(classroom["psuId"]) + "&days=7&date=03/30/2014"
	r = requests.get(url)
	c = r.content
	c = c.split('\n')
	n = len(c)
	cn = c[17].strip()
	cn = cn[51:-16]
	
	print(classroom["classroomName"])

	for x in range(41,n):
		match = re.search('cellspacing', c[x])
		if match:
			currentDay = c[x+4].strip()
			currentDay = currentDay[:11]
			print currentDay
			i = x
			foundColSpan = False;
			while(foundColSpan is not True):
				colMatch = re.search('colspan', c[i])
				#print i
				i+=1
				if colMatch:
					foundColSpan = True

