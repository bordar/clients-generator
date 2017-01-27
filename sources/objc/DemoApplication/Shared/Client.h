//
//  Client.h
//  Borhan
//
//  Created by Pavel on 28.02.12.
//  Copyright (c) 2012 Borhan. All rights reserved.
//

#import <Foundation/Foundation.h>
#import "BorhanClient.h"

#define DEFAULT_SERVICE_URL     @"http://www.borhan.com"

@class BorhanClient;

#ifdef widevine
@class WVSettings;
#endif

@protocol ClientDelegate <NSObject>
-(void) videoStop;
-(void) videoPlay:(NSURL*) url;
-(void) loadWVBitratesList:(NSArray*)wvBitrates;
@end

@interface Client : NSObject <BorhanClientDelegate, ASIProgressDelegate> {
    
    NSMutableArray* mutableArray;
    NSDictionary* dict;
    
    BorhanClient *client;
    NSMutableArray *categories;
    NSMutableArray *media;
    
#ifdef widevine
    WVSettings* wvSettings;
#endif
    
    int partnerId;
    
    
    BorhanUploadToken* token;
    
    long long fileSize;
    long long uploadedSize;
    int currentChunk;
    int uploadTryCount;
    
    NSString *path;
    NSURL *wvUrl;
    NSString *uploadFileTokenId;
    NSString *uploadFilePath;
    
    UISegmentedControl *mBitrates;
    bool mSettingBitRateButton;
    
    UIViewController *uploadDelegateController;
}

+ (Client *)instance;

- (id)initClient;
- (NSArray *)getCategories;
- (NSArray *)getMedia:(BorhanCategory *)category;
- (BOOL)login;
- (NSString *)getThumbPath:(NSString *)fileName;
- (BOOL)isThumbExist:(BorhanMediaEntry *)mediaEntry;
- (BOOL)isThumbExist:(BorhanMediaEntry *)mediaEntry width:(int)width height:(int)height;
- (BOOL)isThumbNameExist:(NSString *)fileName;
- (NSString *)getThumbURL:(NSString *)fileName width:(int)width height:(int)height;
- (NSData *)getThumb:(BorhanMediaEntry *)mediaEntry;
- (NSString *)getShareURL:(BorhanMediaEntry *)mediaEntry;
- (void)shareFacebook:(BorhanMediaEntry *)mediaEntry;
- (void)shareTwitter:(BorhanMediaEntry *)mediaEntry;

- (void)cancelUploading;
- (BOOL)uploadingInProgress;
- (void)uploadProcess:(NSDictionary *)data withDelegate:(UIViewController *)delegateController;
- (NSArray *)getBitratesList:(BorhanMediaEntry *)mediaEntry withFilter:(NSString *)filter;
- (NSString *)getVideoURL:(BorhanMediaEntry *)mediaEntry forMediaEntryDuration:(int)EntryDuration forFlavor:(NSString *)flavorId forFlavorType: (NSString*)flavorType;
- (void)HandleCurrentBitrate:(NSDictionary *)attributes;
- (void)HandleBitrates:(NSDictionary *)attributes;

- (void)donePlayingMovieWithWV;
- (void)playMovieFromUrl:(NSString *)path2;

- (void) initializeWVDictionary: (NSString *)flavorId;
- (void) terminateWV;
- (void)selectBitrate:(int)ind;
- (NSString *)getIframeURL:(BorhanMediaEntry *)mediaEntry;

@property (nonatomic, retain) BorhanClient *client;
@property (nonatomic, retain) NSMutableArray *categories;
@property (nonatomic, retain) NSMutableArray *media;
@property int partnerId;

@property (nonatomic, retain) NSString *uploadFileTokenId;
@property (nonatomic, retain) NSString *uploadFilePath;
@property (nonatomic, retain) NSString *path;
@property (nonatomic, retain) NSURL *wvUrl;
@property (nonatomic, retain) IBOutlet UISegmentedControl *mBitrates;

@property (nonatomic, assign) id<ClientDelegate> delegate;

@property (nonatomic, assign)NSMutableArray* mutableArray;
@property (nonatomic, assign)NSDictionary* dict;

@end
