import pymongo
import urllib2
import linecache
from bson.objectid import ObjectId
from datetime import datetime
from termcolor import colored
import requests
import re
import time

print colored("\n    _____                    _____ _        _      \n\
   |  __ \                  / ____| |      | |      \n\
   | |__) |__ _ __  _ __   | (___ | |_ __ _| |_ ___ \n\
   |  ___/ _ \ '_ \| '_ \   \___ \| __/ _` | __/ _ \ \n\
   | |  |  __/ | | | | | |  ____) | || (_| | ||  __/\n\
   |_|   \___|_| |_|_| |_| |_____/ \__\__,_|\__\___|","blue");
print colored("   | |    (_)                | |  | |               \n\
   | |     ___   _____  ___  | |__| | ___ _ __ ___  \n\
   | |    | \ \ / / _ \/ __| |  __  |/ _ \ '__/ _ \ \n\
   | |____| |\ V /  __/\__ \ | |  | |  __/ | |  __/ \n\
   |______|_| \_/ \___||___/ |_|  |_|\___|_|  \___| \n\
                                                  \n\
                                                  ","white");
print("Running Penn State Classroom update................\n");
today = time.strftime("%m/%d/%Y")

connection = pymongo.Connection()

db = connection["ist"]

db.drop_collection('events')

events = db["events"]

classrooms = db["classrooms"]

cursor = db.classrooms.find()

for classroom in cursor:
	url = "https://clc.its.psu.edu/labhours/RoomPrintout.aspx?&room=" + str(classroom["psuId"]) + "&days=150&date=" + today
	r = requests.get(url)
	c = r.content
	c = c.split('\n')
	n = len(c)
	cn = c[17].strip()
	cn = cn[51:-16]

	recordsAdded = 0
	
	#print colored(classroom["classroomName"],'blue')

	for x in range(41,n):
		match = re.search('cellspacing', c[x])
		if match:
			currentDay = c[x+4].strip()
			currentDay = currentDay[:11]
			#print currentDay
			i = x+1
			foundColSpan = False;
			inTable = False;
			while(foundColSpan is not True):
				i+=1
				# while there are still more classrooms...
				colMatch = re.search('colspan', c[i])
				borderMatch = re.search('table border', c[i])
				endMatch = re.search('/table', c[i])
				trMatch = re.search('<tr>', c[i])
				if colMatch:
					foundColSpan = True
				elif borderMatch:
					inTable = True
				elif endMatch:
					inTable = False
				elif inTable is True:
					if trMatch:
						currentEvent = c[i+6].strip()
						currentEvent = currentEvent[:-12]

						startTime = c[i+2].strip()
						startTime = startTime[:-12]
						startTime = currentDay + " " + startTime
						#Mar 31 2014 12:20 PM
				
						endTime = c[i+4].strip()
						endTime = endTime[:-12]
						endTime = currentDay + " " + endTime
						start = datetime.strptime(startTime, '%b %d %Y %I:%M %p')
						end = datetime.strptime(endTime, '%b %d %Y %I:%M %p')
						event = {
							"name": currentEvent,
							"start": start,
							"end": end,
							"room": classroom["classroomName"]
						}	
						events.insert(event)
						recordsAdded+=1
	print(str(recordsAdded) + " records added for " + str(classroom["classroomName"]))
