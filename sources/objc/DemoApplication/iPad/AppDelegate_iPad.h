//
//  AppDelegate_iPad.h
//  Borhan
//
//  Created by Pavel on 28.02.12.
//  Copyright 2012 Borhan. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface AppDelegate_iPad : NSObject <UIApplicationDelegate> {
    UIWindow *window;
    
    UINavigationController *navigation;
    
    float volumeLevel;
}

@property (nonatomic, retain) IBOutlet UIWindow *window;
@property (nonatomic, retain) UINavigationController *navigation;
@property float volumeLevel;

@end

