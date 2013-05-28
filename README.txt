                                                                     
                                                                     
                                                                     
                                             
TicToc 


Idea: shareSMSride 


Idea is very simple, to propagate a much less utilized service in India, carpooling. 
We are building an SMS bot using KooKoo services, to register everyday SMS users who 
are willing to commute long distance by sharing their own cars or cabs. 

The app will generate notifications when another ride sharing user is found and will also 
facilitate the communication between them to plan their shared trip. Users are also 
provided the functionality to share their reviews through a call. 

Instead of directly letting the users handle the business, we are introducing carpool 
service agencies as a middlemen who can provide cabs to willing carpoolers. Complete DB 
of registered riders is shared with the carpool service agency with their carpool requests, 
which then approves a trip, goes and hires cabs for them. All such updates are notified 
to the users. So, this becomes a complete backend support for any carpooling service 
agency in India. 

All the SMS communication is routed through KooKoo and the reviews are also done by 
voice call services provided by KooKoo. 

Overview of the system with keywords supported: 
All SMS are sent to 09227507512 and all voice calls are to be make to 911166488099.

How the everyday user uses it: 

1. Registration: 
	CARPOOL register <first name> <last name> 
2. Providing information about himself (to be used as a filter while setting people together): 
	CARPOOL info <M/F> <S/NS> 
3. Providing one's own preference for a fellow rider: 
	CARPOOL preference <M/F> <S/NS> 
4. To make a request for a carpool: 
	CARPOOL trip <start city> <end city> <start time> 
5. To group chat with fellow riders of a particular trip: 
	CARPOOL chat <trip ID> <message>
6. To give an review over a voice call:
 Give a call to 911166488099
7. To get a list of all supported keywords:
	CARPOOL help

Functionality provided to the carpool service agent: 

1. Registration: 
	CARPOOL register agent <first name> <last name> 
2. Providing information about himself: 
	CARPOOL info <company name> 
3. Notify all users who have requested for a particular trip when it is approved: 
	CARPOOL notify <trip ID> 
The complete Database sql file has been shared with the app, it goes by the name shareSMSride_DB.sql